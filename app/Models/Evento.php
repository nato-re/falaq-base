<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'descricao', 'data_evento', 'user_id'];

    public function perguntas(): HasMany
    {
        return $this->hasMany(Pergunta::class);
    }
}
