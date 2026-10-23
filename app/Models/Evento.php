<?php

namespace App\Models;

use App\Enums\EventoStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'descricao', 'data_evento', 'user_id', 'status',];

    public function perguntas(): HasMany
    {
        return $this->hasMany(Pergunta::class);
    }

    public function isPublicado(): bool
    {
        return $this->status === EventoStatus::PUBLICADO;
    }

    protected function casts(): array
    {
        return [
           'type' => EventoStatus::class
        ];
    }
}
