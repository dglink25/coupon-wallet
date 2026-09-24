<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Un seul role technique distingue l'administrateur des autres
            // utilisateurs. Un "user" standard peut librement jouer le role
            // d'ambassadeur (en partageant un coupon dont il est proprietaire)
            // et/ou d'acheteur (en passant des commandes) : ce ne sont pas des
            // roles figes mais des usages du meme compte.
            $table->enum('role', ['admin', 'user'])->default('user');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
