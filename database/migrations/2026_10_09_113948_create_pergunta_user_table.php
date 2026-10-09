<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TICKET #009: tabela pivot de votos (Muitos-para-Muitos entre perguntas e usuários).
     */
    public function up(): void
    {
        Schema::create('pergunta_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained('perguntas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Um usuário só pode votar uma vez em cada pergunta
            $table->unique(['pergunta_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergunta_user');
    }
};
