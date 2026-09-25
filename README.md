# Coupon Wallet

Mini-système de coupon promotionnel avec ambassadeurs et portefeuille.
Test technique réalisé pour **General Invasion / YoupiLab** — Septembre 2026.

Un utilisateur « ambassadeur » partage son code promo. Quand un acheteur l'utilise
et marque sa commande comme payée, l'ambassadeur reçoit une commission sur son
portefeuille. Un administrateur gère les coupons et valide les demandes de retrait.

---

## Stack technique

| Composant        | Choix                                                  |
|------------------|--------------------------------------------------------|
| Backend          | Laravel 10 · PHP 8.1+                                  |
| Frontend         | Blade + Bootstrap 5 (CDN, sans build JS)               |
| Base de données  | SQLite                                                 |
| Auth             | Authentification native Laravel (session, sans package tiers) |

---

## Installation

**Prérequis :** PHP >= 8.1, Composer, extension `pdo_sqlite` activée.

```bash
# 1. Cloner le dépôt
git clone <url-du-depot>
cd coupon-wallet

# 2. Installer les dépendances
composer install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Créer la base SQLite si elle n'existe pas
touch database/database.sqlite

# 5. Migrations + seeder (comptes de démo + coupon d'exemple)
php artisan migrate --seed

# 6. Démarrer le serveur de développement
php artisan serve
```

L'application est accessible sur **http://localhost:8000**.

---

## Comptes de démonstration

Le seeder crée automatiquement trois comptes (mot de passe : `password`) :

| Rôle           | E-mail                            |
|----------------|-----------------------------------|
| Administrateur | admin@generalinvasion.com         |
| Ambassadeur    | ambassadeur@generalinvasion.com   |
| Acheteur       | acheteur@generalinvasion.com      |

Un coupon de démonstration `BIENVENUE10` est créé, associé à l'ambassadeur :
10 % de remise globale, dont 50 % à l'acheteur et 50 % de commission.

---

## Parcours de test rapide

1. Connectez-vous en tant qu'**acheteur**.
2. Allez dans *Commandes → Nouvelle commande*, saisissez un montant (ex : 10 000)
   et le code `BIENVENUE10`.
3. Dans la liste des commandes, cliquez sur **Marquer payée** : c'est ce déclencheur
   qui crédite la commission — jamais avant.
4. Connectez-vous en tant qu'**ambassadeur** : la commission apparaît dans
   *Mon portefeuille*.
5. Depuis *Retirer*, envoyez une demande de retrait.
6. Connectez-vous en tant qu'**administrateur** : dans *Retraits*, approuvez
   (totalement ou partiellement avec motif) ou rejetez la demande.
   Le solde n'est débité qu'à ce moment précis.

---

## Tests automatisés

```bash
php artisan test
```

Trois suites Feature couvrent les règles métier sensibles :

- `OrderCommissionTest` — commission jamais créditée avant paiement ; rejouer le
  paiement plusieurs fois ne crédite qu'une seule fois (idempotence).
- `CouponCaseSensitivityTest` — recherche de coupon strictement sensible à la casse.
- `WithdrawalApprovalTest` — solde non débité à la simple demande ; double approbation
  ne débite qu'une fois.

---

## Structure du projet

```
app/
  Http/
    Controllers/
      Admin/          CouponController, WithdrawalController
      Auth/           LoginController
      HomeController, OrderController, WalletController, WithdrawalRequestController
    Middleware/       EnsureUserIsAdmin (alias "admin")
  Models/             User, Wallet, WalletTransaction, Coupon, Order, WithdrawalRequest
  Services/           OrderService, WithdrawalService, WalletException

database/
  migrations/         Schéma relationnel complet (7 migrations)
  seeders/            DatabaseSeeder (3 comptes + 1 coupon)

resources/views/
  layouts/            app.blade.php (layout global)
  auth/               login.blade.php
  orders/             index, create
  wallet/             show
  withdrawals/        create
  admin/
    coupons/          index, create
    withdrawals/      index

routes/web.php        Toutes les routes de l'application
tests/Feature/        Tests des règles métier sensibles
```

---

## Documentation technique

Voir [`DOCUMENTATION_TECHNIQUE.md`](./DOCUMENTATION_TECHNIQUE.md) pour l'architecture
détaillée, les choix de modélisation, et la gestion de l'idempotence et de la
concurrence.

---

*General Invasion Sarl · RCCM : RB/ABC/15 B 828 · Rép. du Bénin*
