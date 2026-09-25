# Documentation technique — Coupon Wallet

> Test technique · General Invasion / YoupiLab · Septembre 2026

---

## 1. Architecture générale

L'application suit l'architecture MVC standard de Laravel, enrichie d'une couche
**Services** dédiée pour isoler toute la logique métier sensible :

```
Requête HTTP
    └── Contrôleur (validation des entrées, autorisation)
            └── Service (règles métier, transactions DB, verrous)
                    └── Modèles Eloquent
                            └── Base de données SQLite
```

Ce découpage a un objectif précis : les contrôleurs ne contiennent **aucun calcul
financier**. Tout est concentré dans `OrderService` et `WithdrawalService`, ce qui
permet de les tester indépendamment de la couche HTTP et d'éviter toute duplication
de règle métier entre contrôleurs.

---

## 2. Modélisation des données

| Table                 | Rôle |
|-----------------------|------|
| `users`               | Comptes unifiés. Le champ `role` (`admin` / `user`) distingue l'administrateur. Ambassadeur et acheteur sont des **usages** du même type de compte, pas des rôles figés. |
| `wallets`             | Un portefeuille par utilisateur (`user_id` unique). Solde en `decimal(12,2)` pour éviter les erreurs d'arrondi. |
| `coupons`             | Code unique, ambassadeur propriétaire, type/valeur de remise, part reversée à l'acheteur, limite d'utilisation, expiration, statut. |
| `orders`              | Commande simplifiée : `amount`, `discount_amount`, `commission_amount`, `status` (`pending`/`paid`), horodatage `commission_credited_at`. |
| `wallet_transactions` | Journal **append-only** de tous les mouvements (crédit commission, débit retrait). Porte la contrainte d'unicité qui garantit l'idempotence. |
| `withdrawal_requests` | Demandes de retrait avec `requested_amount`, `approved_amount`, `status`, `reason`, et traçabilité de l'administrateur traitant. |

### Pourquoi une table `wallet_transactions` séparée ?

L'énoncé exige un historique consultable, mais cette table sert aussi de **journal
d'audit immuable** : on peut reconstituer le solde à partir de la somme des
transactions, indépendamment du champ `balance` courant (valeur dénormalisée pour
la performance des lectures).

### Formule de répartition du coupon

L'énoncé définit une « part reversée à l'acheteur ». Interprétation retenue dans
`Coupon::computeSplit()` :

1. `value` (+ `discount_type`) calcule un **montant total** généré par le coupon
   (pourcentage du montant de commande, ou montant fixe).
2. `buyer_share_percent` détermine la **part reversée à l'acheteur** sous forme
   de remise immédiate.
3. Le reste (`100 − buyer_share_percent`) devient la **commission** créditée à
   l'ambassadeur au paiement.

---

## 3. Authentification et autorisations

Authentification native Laravel (guard `session`), sans package tiers (Breeze,
Jetstream…), conformément à la contrainte « pas de framework JS ».

Le middleware `EnsureUserIsAdmin` (alias `admin`) protège toutes les routes
`/admin/*` : tout accès par un non-administrateur retourne une erreur 403.

---

## 4. Idempotence du crédit de commission

**Règle métier :** « Le crédit de la commission doit être idempotent. »

Garantie à **deux niveaux complémentaires** dans `OrderService::creditAmbassadorCommission()` :

**Niveau applicatif (garde-fou rapide)**
Avant de créditer, on vérifie qu'aucune ligne `wallet_transactions` ne référence déjà
`reference_type = 'order_commission'` et `reference_id = <id commande>`. Si elle existe,
l'opération s'arrête immédiatement — la commission a déjà été créditée.

**Niveau base de données (garantie ultime)**
La table `wallet_transactions` porte une **contrainte d'unicité** sur
`(reference_type, reference_id)`. Si deux requêtes concurrentes passaient toutes les deux
la vérification applicative avant que l'une ait committé, le second `INSERT` échouerait
avec `SQLSTATE 23000`, explicitement intercepté et traité comme un no-op — jamais comme
une erreur remontée à l'utilisateur.

`OrderService::markAsPaid()` applique la même logique au niveau commande : une commande
déjà `paid` ne déclenche plus rien, même rappelée plusieurs fois.

---

## 5. Gestion de la concurrence

**Règle métier :** « Les opérations qui lisent puis modifient un solde doivent être
protégées contre la concurrence. »

Toutes les opérations de lecture-puis-écriture sur un solde sont enveloppées dans une
transaction de base de données (`DB::transaction()`) avec un **verrou pessimiste**
(`lockForUpdate()`) :

- `OrderService::markAsPaid()` — verrouille la ligne `orders` avant de vérifier/modifier
  son statut.
- `OrderService::creditAmbassadorCommission()` — verrouille le coupon puis le wallet de
  l'ambassadeur avant de lire son solde. L'ordre de verrouillage est fixe (coupon → wallet)
  pour éviter les interblocages (deadlocks).
- `WithdrawalService::approve()` — verrouille la `withdrawal_request` puis le wallet avant
  de vérifier le solde et de débiter.
- `WithdrawalService::reject()` — verrouille la `withdrawal_request` pour éviter un
  approve + reject simultané.

Avec SQLite, `lockForUpdate()` dans une transaction sérialise les écritures ; le code
est écrit pour être compatible sans modification avec MySQL ou PostgreSQL en production.

---

## 6. Recherche de coupon sensible à la casse

**Règle métier :** « La recherche d'un coupon par son code doit être sensible à la casse. »

Implémentée dans `Coupon::findByCode()` via :

```php
whereRaw('code = ? COLLATE BINARY', [$code])
```

Sur SQLite, `=` sur une colonne `TEXT` est déjà sensible à la casse par défaut. La clause
`COLLATE BINARY` rend ce comportement **explicite et portable** : sur MySQL avec une
collation `*_ci` par défaut, `=` serait insensible à la casse sans cette précaution.

---

## 7. Retrait — débit uniquement à l'approbation

**Règle métier :** « Un retrait ne débite le portefeuille qu'à l'approbation. »

`WithdrawalService::requestWithdrawal()` crée uniquement une ligne `withdrawal_requests`
avec le statut `pending`. Aucune écriture sur `wallets.balance` n'a lieu à cette étape
(seule une vérification en lecture du solde est faite, pour rejeter immédiatement une
demande manifestement infaisable).

Le débit effectif — création de la `wallet_transaction` de type `debit` et mise à jour
de `wallets.balance` — n'a lieu que dans `WithdrawalService::approve()`, sous verrou,
et seulement si la demande est encore `pending`.

---

## 8. Approbation partielle

Le back-office admin permet de saisir un `approved_amount` inférieur au montant demandé.
Un motif est alors requis et conservé sur la demande pour traçabilité. Le rejet exige
toujours un motif.

---

## 9. Limites et pistes d'évolution

**Verrouillage SQLite**
SQLite ne supporte pas la granularité de verrouillage par ligne de MySQL/PostgreSQL.
Pour un environnement de production à fort trafic, une bascule vers MySQL/PostgreSQL
est recommandée. Le code métier (transactions + `lockForUpdate`) est déjà compatible
sans modification.

**Traitement asynchrone**
Le crédit de commission est aujourd'hui synchrone (dans la même requête que le passage
en « payée »). Pour un volume important, ce traitement pourrait être déporté vers une
file d'attente (`ShouldQueue`) — la contrainte d'unicité sur `wallet_transactions`
continue à garantir l'idempotence même en cas de job rejoué.

**Authentification**
Volontairement minimaliste (pas d'inscription publique, comptes créés via le seeder
ou par l'administrateur) car l'énoncé ne demande pas de parcours d'inscription.
