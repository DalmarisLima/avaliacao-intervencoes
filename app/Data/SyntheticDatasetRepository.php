<?php

namespace App\Data;

use Illuminate\Support\Facades\File;
use RuntimeException;

class SyntheticDatasetRepository
{
    private const TURMA_FILES = [
        '2º Ano A' => '2-ano-a',
        '2o Ano A' => '2-ano-a',
        '1º Ano B' => '1-ano-b',
        '1o Ano B' => '1-ano-b',
        '3º Ano A' => '3-ano-a',
        '3o Ano A' => '3-ano-a',
        'Reforço' => 'reforco',
        'Reforco' => 'reforco',
    ];

    /**
     * @return list<array{nome: string, pre: array<string, int>, pos: array<string, int>}>
     */
    public function forTurma(string $turma, ?string $cenario = null): array
    {
        $slug = $this->resolveSlug($turma);
        $path = $this->resolveDatasetPath($slug, $cenario);

        if (File::exists($path)) {
            $json = File::get($path);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            return TurmaDatasetDefinitions::dedupeByNome($data);
        }

        return $this->fallbackFromDefinitions($slug, $cenario);
    }

    public function caminhoDataset(string $turma, ?string $cenario = null): string
    {
        $slug = $this->resolveSlug($turma);
        $path = $this->resolveDatasetPath($slug, $cenario);

        if (File::exists($path)) {
            return $path;
        }

        return $path.' (fallback PHP)';
    }

    /**
     * @return list<string>
     */
    public function turmasDisponiveis(): array
    {
        return config('turmas.opcoes', []);
    }

    private function resolveSlug(string $turma): string
    {
        foreach (self::TURMA_FILES as $needle => $slug) {
            if (str_contains($turma, $needle)) {
                return $slug;
            }
        }

        return '1-ano-a';
    }

    private function datasetsRoot(): string
    {
        return base_path('data/turmas');
    }

    private function resolveDatasetPath(string $slug, ?string $cenario): string
    {
        $root = $this->datasetsRoot();

        if ($cenario !== null && $cenario !== '') {
            $scenarioPath = "{$root}/{$slug}-{$cenario}.json";
            if (File::exists($scenarioPath)) {
                return $scenarioPath;
            }
        }

        return "{$root}/{$slug}.json";
    }

    /**
     * @return list<array{nome: string, pre: array<string, int>, pos: array<string, int>}>
     */
    private function fallbackFromDefinitions(string $slug, ?string $cenario = null): array
    {
        if ($slug === '2-ano-a' && $cenario === 'dificil') {
            return TurmaDatasetDefinitions::dataset2AnoADificil();
        }

        $method = match ($slug) {
            '1-ano-b' => 'dataset1AnoB',
            '2-ano-a' => 'dataset2AnoAFlexivel',
            '3-ano-a' => 'dataset3AnoA',
            'reforco' => 'datasetReforco',
            default => 'dataset1AnoA',
        };

        if (! method_exists(TurmaDatasetDefinitions::class, $method)) {
            throw new RuntimeException("Dataset não encontrado para a turma [{$slug}].");
        }

        return TurmaDatasetDefinitions::$method();
    }
}
