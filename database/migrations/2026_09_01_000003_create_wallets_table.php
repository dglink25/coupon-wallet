<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // Un portefeuille unique par utilisateur.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Solde disponible, en unite monetaire (2 decimales).
            // decimal(12,2) evite les problemes d'arrondi des flottants.
            $table->decimal('balance', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
