<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /**
     * Somente o dono (criador) do evento pode moderar as perguntas dele.
     */
    public function moderate(User $user, Evento $evento): bool
    {
        return $evento->user_id !== null && $user->id === $evento->user_id;
    }
}
