<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // L'acheteur.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Coupon eventuellement applique (nullable : une commande peut
            // ne pas utiliser de coupon).
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid'])->default('pending');

            // Horodatage du credit de la commission : sert de marqueur
            // metier (en complement de la contrainte d'unicite sur
            // wallet_transactions) pour tracer l'idempotence.
            $table->timestamp('commission_credited_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
