# Documentation technique

## 1. Architecture générale

L'application suit l'architecture MVC standard de Laravel, avec une couche de
**services** dédiée pour isoler la logique métier sensible des contrôleurs :

```
Requête HTTP → Contrôleur (validation, autorisation) → Service (règles métier,
transactions, verrous) → Modèles Eloquent → Base de données SQLite
```

Ce découpage a un objectif précis : les contrôleurs (`OrderController`,
`WithdrawalController`, `WithdrawalRequestController`) ne contiennent **aucune**
logique de calcul financier ou de gestion de concurrence. Tout est concentré
dans `App\Services\OrderService` et `App\Services\WithdrawalService`, ce qui
permet de les tester unitairement/fonctionnellement sans passer par HTTP (voir
`tests/Feature/`) et d'éviter que la même règle métier soit dupliquée (et
divergente) dans plusieurs contrôleurs.

## 2. Modélisation des données

| Table | Rôle |
|---|---|
| `users` | Comptes, avec un champ `role` (`admin` / `user`). Un « ambassadeur » et un « acheteur » sont le même type de compte (`user`) — ce sont des usages, pas des rôles figés, conformément à l'énoncé (« un même utilisateur peut être ambassadeur et/ou acheteur »). |
| `wallets` | Un portefeuille unique par utilisateur (`user_id` unique), avec un `balance` en `decimal(12,2)`. |
| `coupons` | Code unique, ambassadeur propriétaire, type/valeur de remise, part reversée à l'acheteur, limite d'utilisation, expiration, statut actif/inactif. |
| `orders` | Commande simplifiée : `amount`, `discount_amount`, `commission_amount`, `status` (`pending`/`paid`), et un `commission_credited_at` utilisé comme marqueur de traçabilité. |
| `wallet_transactions` | Historique **append-only** de tous les mouvements de portefeuille (crédit commission, débit retrait), avec `balance_after` pour un audit facile. C'est ici que repose la garantie d'idempotence (voir §4). |
| `withdrawal_requests` | Demandes de retrait, avec `requested_amount`, `approved_amount`, `status`, `reason` (motif), et traçabilité de l'administrateur qui a traité la demande. |

### Pourquoi une table `wallet_transactions` séparée plutôt qu'un simple champ `balance` mis à jour directement ?

Parce que l'énoncé exige un **historique des transactions** consultable par
l'utilisateur, mais aussi parce que cette table sert de **journal d'audit
immuable** : en cas de litige sur un solde, on peut toujours reconstituer le
solde à partir de la somme des transactions, indépendamment du champ `balance`
courant de `wallets` (qui reste une valeur dénormalisée pour la performance des
lectures).

### Interprétation retenue pour les champs du coupon

L'énoncé définit un coupon par : « type de remise, valeur, part reversée à
l'acheteur, limite d'utilisation, date d'expiration ». Interprétation
retenue et implémentée dans `Coupon::computeSplit()` :

1. `value` (associée à `discount_type`) détermine un **montant total** généré
   par le coupon sur la commande (pourcentage du montant, ou montant fixe).
2. `buyer_share_percent` définit quelle part de ce montant total est
   **reversée à l'acheteur** sous forme de remise immédiate sur sa commande.
3. Le reste (`100 - buyer_share_percent`) devient la **commission** créditée
   à l'ambassadeur au moment du paiement.

Cette hypothèse est documentée ici explicitement car l'énoncé ne précise pas
la formule exacte de répartition ; elle a été choisie car elle est cohérente
avec la formulation « part reversée à l'acheteur » (sous-entendu : une part
d'un tout, l'autre part restant pour l'ambassadeur).

## 3. Authentification et autorisations

Authentification native Laravel (guard `session`, pas de package tiers type
Breeze/Jetstream, pour rester minimal et sans dépendance frontend JS,
conformément à la contrainte « pas de framework JS »).

Le middleware `App\Http\Middleware\EnsureUserIsAdmin` (alias `admin`) protège
toutes les routes `/admin/*` : seul un utilisateur avec `role = admin` peut y
accéder ; toute autre tentative renvoie une erreur 403.

## 4. Idempotence du crédit de commission

**Règle métier** : « Le crédit de la commission doit être idempotent (pas de
double crédit si l'opération est rejouée). »

La garantie est assurée à **deux niveaux complémentaires**, dans
`OrderService::creditAmbassadorCommission()` :

1. **Niveau applicatif (rapide, mais pas suffisant seul)** : avant de créditer,
   on vérifie qu'aucune ligne `wallet_transactions` ne référence déjà
   `reference_type = 'order_commission'` et `reference_id = <id de la commande>`.
   Si elle existe, on s'arrête immédiatement (l'opération a déjà eu lieu).

2. **Niveau base de données (garantie ultime, résiste à la concurrence)** :
   la table `wallet_transactions` porte une **contrainte d'unicité** sur
   `(reference_type, reference_id)` (migration
   `2026_09_01_000006_create_wallet_transactions_table.php`). Si deux requêtes
   arrivaient en même temps et passaient toutes les deux la vérification
   applicative avant que l'une d'elles ait validé sa transaction, la seconde
   `INSERT` échouerait avec une violation de contrainte (`SQLSTATE 23000`),
   explicitement interceptée et traitée comme un no-op idempotent — **jamais**
   comme une erreur à remonter à l'utilisateur.

Le premier niveau évite l'essentiel des cas (retry applicatif, double-clic
utilisateur, tâche planifiée relancée) sans payer le coût d'une transaction ;
le second niveau protège contre la vraie concurrence (deux workers, deux
requêtes HTTP simultanées) que la seule vérification applicative ne peut pas
couvrir de façon fiable.

`OrderService::markAsPaid()` applique la même logique au niveau de la commande
elle-même : si la commande est déjà `paid`, l'appel est un no-op — la
commission n'est donc jamais recréditée même si `markAsPaid()` est rappelée
sur une commande déjà payée.

## 5. Gestion de la concurrence

**Règle métier** : « Les opérations qui lisent puis modifient un solde doivent
être protégées contre la concurrence (verrouillage). »

Toutes les opérations de lecture-puis-écriture sur un solde sont enveloppées
dans une transaction de base de données (`DB::transaction()`) et utilisent un
**verrou pessimiste** (`lockForUpdate()`) sur les lignes concernées :

- `OrderService::markAsPaid()` verrouille la ligne `orders` concernée avant de
  vérifier/modifier son statut.
- `OrderService::creditAmbassadorCommission()` verrouille le `coupon` puis le
  `wallet` de l'ambassadeur avant de lire son solde et de le mettre à jour.
  L'ordre de verrouillage (coupon puis wallet) est **fixe et documenté** dans
  le code, ce qui évite les interblocages (deadlocks) si plusieurs commissions
  concurrentes impliquaient les mêmes lignes.
- `WithdrawalService::approve()` verrouille la `withdrawal_request` puis le
  `wallet` associé avant de vérifier le solde disponible et de débiter.
- `WithdrawalService::reject()` verrouille la `withdrawal_request` pour éviter
  qu'elle soit traitée deux fois en parallèle (approve + reject simultanés,
  ou deux rejets).

Avec SQLite, `lockForUpdate()` dans une transaction sérialise les écritures
concurrentes sur la même ligne (SQLite verrouille en pratique au niveau
fichier/base pour les écritures) ; l'implémentation reste néanmoins portable
telle quelle vers MySQL/PostgreSQL, où `lockForUpdate()` verrouille
précisément les lignes lues, pour une meilleure concurrence en production.

## 6. Recherche de coupon sensible à la casse

**Règle métier** : « La recherche d'un coupon par son code doit être sensible
à la casse. »

Implémentée dans `Coupon::findByCode()` via
`whereRaw('code = ? COLLATE BINARY', [$code])`. Sur SQLite, l'opérateur `=`
sur une colonne `TEXT` est déjà sensible à la casse par défaut (à la
différence de `LIKE`) ; la clause `COLLATE BINARY` explicite ce comportement
et le rend indépendant du moteur de base de données choisi en production (une
collation `*_ci` par défaut sur MySQL, par exemple, rendrait `=` insensible à
la casse sans cette précaution).

## 7. Retrait : le solde n'est débité qu'à l'approbation

**Règle métier** : « Un retrait ne débite le portefeuille qu'à l'approbation
de l'administrateur, jamais à la simple demande. »

`WithdrawalService::requestWithdrawal()` se contente de créer une ligne
`withdrawal_requests` avec le statut `pending` : **aucune écriture** sur
`wallets.balance` n'a lieu à cette étape (seule une vérification en lecture du
solde disponible est faite, pour rejeter immédiatement une demande
manifestement infaisable). Le débit effectif — création de la
`wallet_transaction` de type `debit` et mise à jour de `wallets.balance` — n'a
lieu que dans `WithdrawalService::approve()`, sous verrou, et seulement si la
demande est encore `pending` (garde-fou d'idempotence identique à celui du
crédit de commission : une demande déjà traitée ne peut plus être re-traitée).

## 8. Approbation partielle et motif

Le formulaire d'approbation (back-office) permet à l'administrateur de saisir
un `approved_amount` différent du montant demandé. Si le montant approuvé est
inférieur au montant demandé, un motif (`reason`) est requis côté formulaire
(`required_if:is_partial,1` côté validation logique) et conservé sur la
demande pour traçabilité. Le rejet exige toujours un motif.

## 9. Limites connues et pistes d'amélioration

- **Verrouillage SQLite** : SQLite ne supporte pas nativement le
  multi-écriture concurrent aussi finement que MySQL/PostgreSQL. Pour un
  environnement de production à fort trafic, une bascule vers MySQL/PostgreSQL
  est recommandée ; le code métier (transactions + `lockForUpdate`) est déjà
  écrit pour être compatible sans modification.
- **File d'attente** : le crédit de commission est aujourd'hui synchrone (dans
  la même requête que le passage en « payée »). Pour un volume important, ce
  traitement pourrait être déporté vers une file d'attente (`ShouldQueue`),
  la contrainte d'unicité sur `wallet_transactions` continuant à garantir
  l'idempotence même en cas de job rejoué après échec.
- **Authentification** : volontairement minimaliste (pas d'inscription
  publique, comptes créés via le seeder ou, en production, par un
  administrateur) car l'énoncé ne demande pas de parcours d'inscription.
