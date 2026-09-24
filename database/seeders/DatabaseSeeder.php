<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Cree, au minimum, un compte admin, un ambassadeur et un acheteur,
     * chacun avec un portefeuille initialise a zero, ainsi qu'un coupon de
     * demonstration pret a l'emploi.
     *
     * Identifiants (mot de passe identique pour tous : "password") :
     *   - admin@generalinvasion.com        (administrateur)
     *   - ambassadeur@generalinvasion.com  (ambassadeur)
     *   - acheteur@generalinvasion.com     (acheteur)
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@generalinvasion.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        Wallet::create(['user_id' => $admin->id, 'balance' => 0]);

        $ambassador = User::create([
            'name' => 'Aicha Ambassadrice',
            'email' => 'ambassadeur@generalinvasion.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        Wallet::create(['user_id' => $ambassador->id, 'balance' => 0]);

        $buyer = User::create([
            'name' => 'Bruno Acheteur',
            'email' => 'acheteur@generalinvasion.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        Wallet::create(['user_id' => $buyer->id, 'balance' => 0]);

        // Coupon de demonstration : 10% de remise globale sur le montant de
        // la commande, dont 50% reverses a l'acheteur (remise immediate) et
        // 50% credites en commission a l'ambassadeur au paiement.
        Coupon::create([
            'code' => 'BIENVENUE10',
            'ambassador_user_id' => $ambassador->id,
            'discount_type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'buyer_share_percent' => 50,
            'usage_limit' => 100,
            'usage_count' => 0,
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);
    }
}
