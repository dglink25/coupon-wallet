<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Cree une commande simulee pour un acheteur, en appliquant
     * eventuellement un code coupon.
     *
     * L'application du coupon ne fait ici que calculer et figer la remise ;
     * aucune commission n'est encore creditee (elle ne le sera qu'au
     * paiement, voir markAsPaid()).
     */
    public function createOrder(User $buyer, float $amount, ?string $couponCode = null): Order
    {
        if ($amount <= 0) {
            throw new WalletException('Le montant de la commande doit etre positif.');
        }

        $coupon = null;
        $discount = 0.0;
        $commission = 0.0;

        if ($couponCode) {
            $coupon = Coupon::findByCode($couponCode);

            if (! $coupon) {
                throw new WalletException("Code coupon inconnu : {$couponCode}.");
            }

            if (! $coupon->isUsable()) {
                throw new WalletException('Ce coupon n\'est plus valide (inactif, expire ou limite atteinte).');
            }

            $split = $coupon->computeSplit($amount);
            $discount = $split['discount'];
            $commission = $split['commission'];
        }

        return Order::create([
            'user_id' => $buyer->id,
            'coupon_id' => $coupon?->id,
            'amount' => $amount,
            'discount_amount' => $discount,
            'commission_amount' => $commission,
            'status' => Order::STATUS_PENDING,
        ]);
    }

    /**
     * Marque une commande comme payee et declenche, le cas echeant, le
     * credit de la commission de l'ambassadeur.
     *
     * Regles metier appliquees ici :
     *  - la commission n'est jamais creditee avant le passage a "payee" ;
     *  - l'operation est idempotente : rejouer markAsPaid() sur une
     *    commande deja payee ne credite jamais une seconde fois ;
     *  - les lectures-puis-ecritures sur le solde du portefeuille sont
     *    protegees par un verrou pessimiste (lockForUpdate) au sein d'une
     *    transaction, pour rester correctes sous acces concurrents.
     */
    public function markAsPaid(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            // On relit la commande avec un verrou pour se proteger contre
            // deux requetes qui tenteraient de la payer en meme temps.
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->isPaid()) {
                // Idempotence : rejouer l'action sur une commande deja
                // payee ne fait rien de plus.
                return $locked;
            }

            $locked->status = Order::STATUS_PAID;
            $locked->paid_at = now();
            $locked->save();

            if ($locked->coupon_id && (float) $locked->commission_amount > 0) {
                $this->creditAmbassadorCommission($locked);
            }

            return $locked->fresh();
        });
    }

    /**
     * Credite la commission de l'ambassadeur sur son portefeuille, de
     * maniere idempotente et protegee contre la concurrence.
     */
    protected function creditAmbassadorCommission(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Garde-fou n°1 (applicatif) : si l'operation a deja ete
            // rejouee, on s'arrete immediatement.
            $alreadyCredited = WalletTransaction::query()
                ->where('reference_type', 'order_commission')
                ->where('reference_id', $order->id)
                ->exists();

            if ($alreadyCredited) {
                return;
            }

            /** @var Coupon $coupon */
            $coupon = Coupon::query()->lockForUpdate()->findOrFail($order->coupon_id);

            // Verrouillage pessimiste du portefeuille de l'ambassadeur : la
            // ligne est bloquee jusqu'a la fin de la transaction, ce qui
            // empeche deux credits (ou un credit et un retrait) de lire le
            // meme solde de depart et de se marcher dessus.
            $wallet = Wallet::query()
                ->lockForUpdate()
                ->where('user_id', $coupon->ambassador_user_id)
                ->firstOrFail();

            $commission = (float) $order->commission_amount;
            $newBalance = round((float) $wallet->balance + $commission, 2);

            try {
                // Garde-fou n°2 (base de donnees) : la contrainte unique sur
                // (reference_type, reference_id) est l'ultime rempart contre
                // le double credit, y compris en cas de course entre deux
                // workers qui passeraient tous les deux le garde-fou n°1
                // avant que l'un des deux ait pu committer.
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransaction::TYPE_CREDIT,
                    'amount' => $commission,
                    'balance_after' => $newBalance,
                    'description' => "Commission ambassadeur - commande #{$order->id}",
                    'reference_type' => 'order_commission',
                    'reference_id' => $order->id,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Violation de la contrainte unique : une autre transaction
                // a deja credite cette commission entre-temps. On considere
                // l'operation comme deja effectuee (idempotence) et on
                // n'applique donc pas le solde une deuxieme fois.
                if ($this->isUniqueConstraintViolation($e)) {
                    return;
                }

                throw $e;
            }

            $wallet->balance = $newBalance;
            $wallet->save();

            $coupon->usage_count = $coupon->usage_count + 1;
            $coupon->save();

            $order->commission_credited_at = now();
            $order->saveQuietly();
        });
    }

    protected function isUniqueConstraintViolation(\Illuminate\Database\QueryException $e): bool
    {
        // Code SQLSTATE 23000 = violation de contrainte d'integrite,
        // commun a SQLite et MySQL pour les contraintes uniques.
        return $e->getCode() === '23000';
    }
}
