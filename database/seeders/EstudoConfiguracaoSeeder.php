<?php

namespace Database\Seeders;

use App\Models\EstudoConfiguracao;
use Illuminate\Database\Seeder;

class EstudoConfiguracaoSeeder extends Seeder
{
    public function run(): void
    {
        $conteudos = config('intervencao.conteudos_intervencao', []);

        EstudoConfiguracao::query()->updateOrCreate(
            ['id' => 1],
            [
                'titulo' => config('intervencao.app_titulo'),
                'objetivo' => config('intervencao.app_descricao'),
                'conteudo_intervencao' => $conteudos['flexivel'] ?? null,
                'conteudos_intervencao' => $conteudos,
            ]
        );
    }
}
