# Coupon Wallet — Mini-système de coupon promotionnel avec ambassadeurs et portefeuille

Test technique réalisé pour **General Invasion / YoupiLab** — Septembre 2026.

Application Laravel permettant à des « ambassadeurs » de partager un code promo
personnel : lorsqu'un « acheteur » l'utilise sur une commande payée, l'ambassadeur
reçoit une commission créditée sur un portefeuille (wallet), qu'il peut ensuite
demander à retirer (validation par un administrateur).

## Sommaire

- [Stack technique](#stack-technique)
- [Installation](#installation)
- [Comptes de démonstration](#comptes-de-démonstration)
- [Parcours de test rapide](#parcours-de-test-rapide)
- [Tests automatisés](#tests-automatisés)
- [Structure du projet](#structure-du-projet)
- [Documentation technique](#documentation-technique)

## Stack technique

| Composant   | Choix                              |
|-------------|-------------------------------------|
| Backend     | Laravel 10 (PHP 8.1+)               |
| Frontend    | Blade + Bootstrap 5 (CDN, sans build JS) |
| Base de données | SQLite                          |
| Auth        | Système d'authentification natif Laravel (session, sans package tiers) |

## Installation

Prérequis : PHP >= 8.1, Composer, extension `pdo_sqlite` activée.

```bash
# 1. Cloner le dépôt
git clone <url-du-depot>
cd coupon-wallet

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement et générer la clé d'application
cp .env.example .env
php artisan key:generate

# 4. Créer la base de données SQLite (le fichier existe déjà dans le dépôt,
#    sinon il suffit de le créer manuellement) :
touch database/database.sqlite

# 5. Lancer les migrations + le seeder (comptes de démo + coupon d'exemple)
php artisan migrate --seed

# 6. Démarrer le serveur de développement
php artisan serve
```

L'application est alors accessible sur **http://localhost:8000**.

## Comptes de démonstration

Le seeder (`database/seeders/DatabaseSeeder.php`) crée automatiquement :

| Rôle         | E-mail                          | Mot de passe |
|--------------|----------------------------------|--------------|
| Administrateur | admin@generalinvasion.com      | `password`   |
| Ambassadeur  | ambassadeur@generalinvasion.com  | `password`   |
| Acheteur     | acheteur@generalinvasion.com     | `password`   |

Un coupon de démonstration `BIENVENUE10` est également créé, associé à
l'ambassadeur, avec 10 % de remise globale (dont 50 % reversés à l'acheteur,
50 % de commission pour l'ambassadeur).

## Parcours de test rapide

1. **Connectez-vous en tant qu'acheteur** (`acheteur@generalinvasion.com`).
2. Allez dans *Mes commandes → + Nouvelle commande*, saisissez un montant
   (ex : 10 000) et le code `BIENVENUE10`.
3. Dans la liste des commandes, cliquez sur *Marquer comme payée* : c'est ce
   déclencheur qui applique la remise et crédite la commission — jamais avant.
4. **Déconnectez-vous et reconnectez-vous en tant qu'ambassadeur**
   (`ambassadeur@generalinvasion.com`) : la commission apparaît dans
   *Mon portefeuille*.
5. Depuis *Mon portefeuille → Demander un retrait*, envoyez une demande.
6. **Reconnectez-vous en tant qu'administrateur** : dans *Demandes de retrait*,
   approuvez (totalement ou partiellement, avec motif) ou rejetez la demande.
   Le solde de l'ambassadeur n'est débité qu'à ce moment précis.

## Tests automatisés

Le projet inclut des tests Feature ciblant spécifiquement les règles métier
sensibles demandées dans l'énoncé (idempotence, non-anticipation du crédit,
sensibilité à la casse) :

```bash
php artisan test
```

- `tests/Feature/OrderCommissionTest.php` — la commission n'est jamais créditée
  avant paiement, et rejouer le paiement plusieurs fois ne crédite qu'une fois.
- `tests/Feature/CouponCaseSensitivityTest.php` — recherche de coupon sensible
  à la casse.
- `tests/Feature/WithdrawalApprovalTest.php` — le solde n'est débité qu'à
  l'approbation admin, jamais à la simple demande, et une double-approbation
  ne débite qu'une fois.

## Structure du projet

```
app/
  Http/Controllers/        Contrôleurs (Auth, Admin, utilisateur)
  Http/Middleware/         Middleware "admin" pour le back-office
  Models/                  User, Wallet, WalletTransaction, Coupon, Order, WithdrawalRequest
  Services/                OrderService, WithdrawalService (logique métier + concurrence + idempotence)
database/
  migrations/               Schéma relationnel complet
  seeders/                  Comptes de démo + coupon d'exemple
resources/views/           Vues Blade (back-office admin + interface utilisateur)
routes/web.php             Toutes les routes de l'application
tests/Feature/             Tests des règles métier sensibles
```

## Documentation technique

Voir [`DOCUMENTATION_TECHNIQUE.md`](./DOCUMENTATION_TECHNIQUE.md) pour le
détail de l'architecture, des choix de modélisation, et surtout de la gestion
de l'idempotence et de la concurrence sur les opérations sensibles (crédit de
commission, débit de retrait).

---

*General Invasion Sarl — RCCM : RB/ABC/15 B 828 — Rép. du Bénin*
