<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    /**
     * Cree une demande de retrait. Le solde n'est PAS debite ici : il ne le
     * sera qu'a l'approbation par un administrateur.
     */
    public function requestWithdrawal(User $user, float $amount): WithdrawalRequest
    {
        if ($amount <= 0) {
            throw new WalletException('Le montant demande doit etre positif.');
        }

        $wallet = $user->wallet;

        if (! $wallet || (float) $wallet->balance < $amount) {
            throw new WalletException('Solde disponible insuffisant pour cette demande.');
        }

        return WithdrawalRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'requested_amount' => $amount,
            'status' => WithdrawalRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Approuve une demande de retrait (totalement ou partiellement) et
     * debite le portefeuille en consequence.
     *
     * - Verrouillage pessimiste de la demande ET du portefeuille pour
     *   eviter qu'un admin double-clic (ou deux admins simultanement)
     *   ne debite deux fois le meme retrait.
     * - Idempotence : une demande qui n'est plus "pending" (deja traitee)
     *   est ignoree silencieusement, elle n'est jamais retraitee.
     */
    public function approve(WithdrawalRequest $withdrawal, float $approvedAmount, User $admin, ?string $reason = null): WithdrawalRequest
    {
        if ($approvedAmount <= 0) {
            throw new WalletException('Le montant approuve doit etre positif.');
        }

        return DB::transaction(function () use ($withdrawal, $approvedAmount, $admin, $reason) {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if (! $locked->isPending()) {
                // Idempotence : la demande a deja ete approuvee ou rejetee,
                // on ne rejoue jamais le debit.
                return $locked;
            }

            $wallet = $locked->wallet()->lockForUpdate()->firstOrFail();

            if ((float) $wallet->balance < $approvedAmount) {
                throw new WalletException('Solde du portefeuille insuffisant pour approuver ce montant.');
            }

            $newBalance = round((float) $wallet->balance - $approvedAmount, 2);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $approvedAmount,
                'balance_after' => $newBalance,
                'description' => "Retrait approuve - demande #{$locked->id}",
                'reference_type' => 'withdrawal',
                'reference_id' => $locked->id,
            ]);

            $wallet->balance = $newBalance;
            $wallet->save();

            $isPartial = $approvedAmount < (float) $locked->requested_amount;

            $locked->status = WithdrawalRequest::STATUS_APPROVED;
            $locked->approved_amount = $approvedAmount;
            $locked->reason = $isPartial ? $reason : $locked->reason;
            $locked->processed_by = $admin->id;
            $locked->processed_at = now();
            $locked->save();

            return $locked->fresh();
        });
    }

    /**
     * Rejette une demande de retrait. Aucun mouvement de portefeuille.
     * Idempotent au meme titre que approve().
     */
    public function reject(WithdrawalRequest $withdrawal, User $admin, string $reason): WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawal, $admin, $reason) {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if (! $locked->isPending()) {
                return $locked;
            }

            $locked->status = WithdrawalRequest::STATUS_REJECTED;
            $locked->reason = $reason;
            $locked->processed_by = $admin->id;
            $locked->processed_at = now();
            $locked->save();

            return $locked->fresh();
        });
    }
}
