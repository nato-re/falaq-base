<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moderacao extends Model
{
    use HasFactory;

    /**
     * O Laravel pluralizaria "Moderacao" como "moderacaos" (regra do inglês),
     * por isso o nome da tabela é definido explicitamente.
     */
    protected $table = 'moderacoes';

    protected $fillable = ['pergunta_id', 'motivo', 'status'];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }
}
