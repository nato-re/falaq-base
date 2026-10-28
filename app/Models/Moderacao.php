<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moderacao extends Model
{
    /**
     * Status aceitos para uma moderação.
     */
    public const STATUS = ['analisando', 'aprovada', 'rejeitada'];

    /**
     * O pluralizador do Laravel (em inglês) geraria "moderacaos",
     * por isso o nome da tabela é definido explicitamente.
     */
    protected $table = 'moderacoes';

    protected $fillable = ['pergunta_id', 'motivo', 'status'];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }
}
