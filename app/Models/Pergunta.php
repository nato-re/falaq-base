<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pergunta extends Model
{
    use HasFactory;

    protected $fillable = ['evento_id', 'texto', 'status', 'user_id', 'is_public'];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function moderacoes(): HasMany
    {
        return $this->hasMany(Moderacao::class);
    }
}
