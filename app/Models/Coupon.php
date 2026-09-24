<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'ambassador_user_id',
        'discount_type',
        'value',
        'buyer_share_percent',
        'usage_limit',
        'usage_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ambassador_user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Recherche d'un coupon par son code, de maniere strictement sensible
     * a la casse ("PROMO10" != "promo10").
     *
     * Sur SQLite, l'operateur "=" appose sur une colonne TEXT est deja
     * sensible a la casse par defaut (contrairement a LIKE). On utilise
     * neanmoins une comparaison binaire explicite pour que le comportement
     * reste garanti si la base de donnees venait a changer (ex: MySQL avec
     * une collation *_ci par defaut).
     */
    public static function findByCode(string $code): ?self
    {
        return static::query()
            ->whereRaw('code = ? COLLATE BINARY', [$code])
            ->first();
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calcule la remise accordee a l'acheteur et la commission de
     * l'ambassadeur pour un montant de commande donne.
     *
     * @return array{discount: float, commission: float}
     */
    public function computeSplit(float $orderAmount): array
    {
        $pool = $this->discount_type === self::TYPE_PERCENTAGE
            ? round($orderAmount * ((float) $this->value / 100), 2)
            : (float) $this->value;

        // La remise/commission totale ne peut jamais depasser le montant
        // de la commande.
        $pool = min($pool, $orderAmount);

        $discount = round($pool * ($this->buyer_share_percent / 100), 2);
        $commission = round($pool - $discount, 2);

        return [
            'discount' => $discount,
            'commission' => $commission,
        ];
    }
}
