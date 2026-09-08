<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PerguntaSeeder extends Seeder
{
    public function run(): void
    {


        // Injeta 5.000 perguntas de teste no evento principal para simular a carga pesada
        $perguntas = [];
        $agora = Carbon::now();

        for ($i = 1; $i <= 5000; $i++) {
            $perguntas[] = [
                'evento_id'  => 1,
                'user_id' => 1,
                'texto'      => "Pergunta de teste #{$i}: Como a arquitetura lida com alta demanda de acessos simultâneos?",
                'status'     => 'pendente',
                'created_at' => $agora->copy()->subSeconds(5000 - $i),
                'updated_at' => $agora->copy()->subSeconds(5000 - $i),
            ];

            if ($i % 500 === 0) {
                Pergunta::insert($perguntas);
                $perguntas = [];
            }
        }

        // Injeta 5 perguntas no evento secundário
        for ($j = 1; $j <= 5; $j++) {
            Pergunta::create([
                'evento_id'  => 2,
                'user_id' => 1,
                'texto'      => "Pergunta do workshop #{$j}: O que é o Service Container?",
                'status'     => 'aprovado',
            ]);
        }
    }
}
