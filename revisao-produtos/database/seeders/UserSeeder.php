<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Ana Vendedora',  'email' => 'ana@loja.test',   'tipo' => 'vendedor'],
            ['name' => 'Bruno Vendedor', 'email' => 'bruno@loja.test', 'tipo' => 'vendedor'],
            ['name' => 'Carla Cliente',  'email' => 'carla@loja.test', 'tipo' => 'cliente'],
            ['name' => 'Diego Cliente',  'email' => 'diego@loja.test', 'tipo' => 'cliente'],
        ];

        foreach ($usuarios as $usuario) {
            User::create($usuario + ['password' => 'senha123']);
        }
    }
}
