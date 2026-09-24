<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();

            // reference_type + reference_id identifient l'operation metier
            // a l'origine de la transaction (ex: "order_commission" + id de
            // la commande, ou "withdrawal" + id de la demande de retrait).
            //
            // L'index unique ci-dessous est le garde-fou d'idempotence au
            // niveau base de donnees : meme si le code applicatif est
            // rejoue (retry, requete dupliquee, deux workers concurrents),
            // une deuxieme tentative de credit pour la MEME commande ne
            // pourra jamais inserer une deuxieme ligne.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamps();

            $table->unique(['reference_type', 'reference_id'], 'wallet_tx_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
