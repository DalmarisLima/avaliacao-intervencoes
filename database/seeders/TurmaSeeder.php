<?php

namespace Database\Seeders;

use App\Data\SyntheticDatasetRepository;
use App\Data\TurmaDatasetDefinitions;
use App\Models\Aluno;
use App\Models\Intervencao;
use App\Models\Turma;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            '1º Ano A' => '1-ano-a',
            '1º Ano B' => '1-ano-b',
            '2º Ano A' => '2-ano-a',
            '3º Ano A' => '3-ano-a',
            'Turma Reforço' => 'reforco',
        ];

        foreach ($map as $nome => $slug) {
            $turma = Turma::updateOrCreate(
                ['slug' => $slug],
                ['nome' => $nome]
            );

            $dataset = $this->loadDataset($slug, $nome);

            foreach ($dataset as $idx => $row) {
                Aluno::updateOrCreate(
                    [
                        'turma_id' => $turma->id,
                        'numero' => $idx + 1,
                    ],
                    ['nome' => $row['nome']]
                );
            }

            Intervencao::where('turma', $nome)->update(['turma_id' => $turma->id]);
        }
    }

    /**
     * @return list<array{nome: string}>
     */
    private function loadDataset(string $slug, string $nome): array
    {
        $repo = new SyntheticDatasetRepository;

        return $repo->forTurma($nome);
    }
}
