<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrador',
            'tipo' => '1',
            'email' => 'admin@exemple.com',
            'password' => 'admin',
            'administrador' => 1,
            'res_chamados' => 1,
            'lista_departamento_users' => '[]',
            'vip' => 1
        ]);
    }
}
