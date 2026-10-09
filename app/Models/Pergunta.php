<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pergunta extends Model
{
    use HasFactory;

    protected $fillable = ['evento_id', 'texto', 'status', 'is_public'];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    /**
     * Autor da pergunta (coluna perguntas.user_id).
     * Necessário porque EventoController@show já carrega with('user').
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * TICKET #010: usuários que votaram nesta pergunta (pivot pergunta_user).
     */
    public function votos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pergunta_user')->withTimestamps();
    }
}
