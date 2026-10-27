<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    public function moderate(User $user, Evento $evento): bool
    {
        return $user->id === $evento->user_id;
    }
}