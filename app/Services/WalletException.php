<?php

namespace App\Services;

use RuntimeException;

/**
 * Exception metier levee par les services Wallet/Order/Withdrawal pour
 * signaler une violation de regle metier (solde insuffisant, coupon
 * invalide, etat incoherent...). Elle est capturee dans les controleurs
 * pour etre traduite en message utilisateur.
 */
class WalletException extends RuntimeException
{
}
