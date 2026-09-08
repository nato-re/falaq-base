<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('data_evento')->nullable();
            $table->timestamps();
        });

        Schema::create('perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->nullable();
            $table->text('texto');
            $table->string('status')->default('pendente');
            $table->timestamps();x
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perguntas');
        Schema::dropIfExists('eventos');
    }
};
