<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * CONTRATO DO R1: este seeder define o nome da model (Produto), da tabela
 * (produtos) e das colunas. Sua migration e sua model precisam ser
 * compatíveis com ele. Não altere este arquivo.
 */
class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = [
            ['nome' => 'Fone Bluetooth', 'categoria' => 'Eletrônicos', 'garantia_meses' => 12, 'preco' => 129.9, 'estoque' => 15],
            ['nome' => 'Camiseta Básica', 'categoria' => 'Vestuário', 'garantia_meses' => 0, 'preco' => 39.9, 'estoque' => 30],
            ['nome' => 'Jogo de Panelas', 'categoria' => 'Casa', 'garantia_meses' => 24, 'preco' => 299.9, 'estoque' => 6],
            ['nome' => 'Café em Grãos 1 kg', 'categoria' => 'Alimentos', 'garantia_meses' => 0, 'preco' => 54.9, 'estoque' => 20],
            ['nome' => 'Carregador USB-C', 'categoria' => 'Eletrônicos', 'garantia_meses' => 6, 'preco' => 79.9, 'estoque' => 12],
        ];

        $colunas = [...array_keys($produtos[0]), 'vendedor_id'];

        if (! class_exists(Produto::class) || ! Schema::hasColumns('produtos', $colunas)) {
            $this->command->warn('Produtos não semeados: faça o R1 (model Produto + migration com as colunas '
                . implode(', ', $colunas) . ') e rode "php artisan migrate:fresh --seed".');

            return;
        }

        $vendedores = User::where('tipo', 'vendedor')->orderBy('id')->pluck('id');

        foreach ($produtos as $i => $dados) {
            Produto::forceCreate($dados + ['vendedor_id' => $vendedores[$i % $vendedores->count()]]);
        }
    }
}
