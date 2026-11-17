<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// R7 — criado com: php artisan make:model Venda
class Venda extends Model
{
    // Sem isso o Laravel procuraria a tabela "vendas" mesmo — o plural
    // automático (só acrescenta "s") funciona aqui. Deixo explícito por segurança.
    protected $table = 'vendas';

    protected $fillable = ['cliente_id'];

    // R7 — N:M com Produto pela pivô produto_venda.
    // withPivot: as colunas extras da pivô que queremos ler ($produto->pivot->quantidade).
    // withTimestamps: preenche created_at/updated_at da pivô no attach.
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_venda')
            ->withPivot('quantidade', 'preco_unitario')
            ->withTimestamps();
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
