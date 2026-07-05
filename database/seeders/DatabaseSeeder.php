<?php

namespace Database\Seeders;

use App\Models\EstudoConfiguracao;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Professor Demo',
            'email' => 'professor@example.com',
            'password' => 'password',
        ]);

        $this->call(TurmaSeeder::class);
        $this->call(EstudoConfiguracaoSeeder::class);
    }
}
