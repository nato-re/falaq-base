<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// R1 — criado com: php artisan make:model Produto
class Produto extends Model
{
    // Colunas que podem ser preenchidas em massa (Produto::create($dados)).
    // vendedor_id entra aqui porque o controller o adiciona (R6).
    protected $fillable = [
        'nome',
        'categoria',
        'garantia_meses',
        'preco',
        'estoque',
        'vendedor_id',
    ];

    // R1 — cada produto PERTENCE a um usuário (o vendedor).
    // 2º parâmetro = nome da FK, necessário porque não é "user_id".
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}
