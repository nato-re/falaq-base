<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /**
     * TICKET #016: somente o proprietário do evento pode moderar
     * (listar moderações e alterar o status delas).
     *
     * Descoberta automática do Laravel: App\Models\Evento -> App\Policies\EventoPolicy.
     */
    public function moderate(User $user, Evento $evento): bool
    {
        return (int) $evento->user_id === (int) $user->id;
    }
}
