<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            // Code partage par l'ambassadeur. La comparaison doit rester
            // sensible a la casse (voir Coupon::findByCode()) : sur SQLite,
            // l'operateur "=" est nativement sensible a la casse pour du
            // texte, ce qui satisfait directement l'exigence metier.
            $table->string('code')->unique();

            $table->foreignId('ambassador_user_id')->constrained('users')->cascadeOnDelete();

            $table->enum('discount_type', ['percentage', 'fixed']);

            // Valeur globale de la remise/commission generee par le coupon :
            // un pourcentage du montant de la commande (discount_type =
            // percentage) ou un montant fixe (discount_type = fixed).
            $table->decimal('value', 12, 2);

            // Part (en %) de cette valeur globale qui est reversee a
            // l'acheteur sous forme de remise sur sa commande. Le reste
            // (100 - buyer_share_percent) constitue la commission creditee
            // a l'ambassadeur lorsque la commande est payee.
            $table->unsignedTinyInteger('buyer_share_percent')->default(50);

            // Nombre maximum d'utilisations reussies (commandes payees).
            // Null = illimite.
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);

            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
