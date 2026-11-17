<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// R1 — criado com: php artisan make:migration create_produtos_table
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');                    // texto até 255
            $table->string('categoria');               // Eletrônicos | Vestuário | Casa | Alimentos
            $table->integer('garantia_meses');         // 0 a 36
            $table->decimal('preco', 8, 2);            // decimal (8,2)
            $table->integer('estoque');                // >= 0
            // FK para users. constrained('users') porque o nome da coluna
            // (vendedor_id) não segue o padrão "user_id".
            $table->foreignId('vendedor_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
