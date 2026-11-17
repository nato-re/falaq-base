<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// R7 — criado com: php artisan make:migration create_vendas_table
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
