<?php

namespace App\Enums;

enum EventoStatus: string
{
    case RASCUNHO = 'rascunho';
    case PUBLICADO = 'publicado';
    case ENCERRADO = 'encerrado';

}
