<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// R7 — tabela PIVÔ da relação N:M entre produtos e vendas.
// Criado com: php artisan make:migration create_produto_venda_table
// Nome segue a convenção do Laravel: as duas tabelas no singular,
// em ordem alfabética, separadas por "_"  →  produto_venda
// IMPORTANTE: esta migration precisa rodar DEPOIS de produtos e vendas.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('quantidade');
            $table->decimal('preco_unitario', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_venda');
    }
};
