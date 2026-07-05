<?php

namespace App\Console\Commands;

use App\Data\SyntheticDatasetRepository;
use App\Models\Intervencao;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Console\Command;

class IntervencoesRegenerarDadosSinteticosCommand extends Command
{
    protected $signature = 'intervencoes:regenerar-dados-sinteticos
                            {--turma= : Regenera só intervenções desta turma (ex.: "2º Ano A")}
                            {--dry-run : Apenas mostra o que seria regenerado}
                            {--force : Confirma sem perguntar}';

    protected $description = 'Regenera avaliações PRÉ/PÓS sintéticas usando os datasets atuais, sem alterar o questionário.';

    public function handle(
        SyntheticEvaluationGenerator $generator,
        SyntheticDatasetRepository $datasets,
    ): int {
        $query = Intervencao::query()->orderBy('id');

        if ($turma = $this->option('turma')) {
            $query->where('turma', $turma);
        }

        $intervencoes = $query->get();

        if ($intervencoes->isEmpty()) {
            $this->warn('Nenhuma intervenção encontrada.');

            return self::SUCCESS;
        }

        $this->info('Intervenções que serão regeneradas:');
        foreach ($intervencoes as $intervencao) {
            $datasetPath = $datasets->caminhoDataset(
                (string) $intervencao->turma,
                (string) $intervencao->cenario
            );

            $this->line(sprintf(
                '  - #%d %s (%s / %s) → %s',
                $intervencao->id,
                $intervencao->titulo,
                $intervencao->turma,
                $intervencao->cenario,
                $datasetPath
            ));
        }

        $this->newLine();
        $this->info('Preservado: questionário, etapas do fluxo, respostas e participantes.');

        if ($this->option('dry-run')) {
            $this->warn('Modo simulação. Execute sem --dry-run para regenerar.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Confirma a regeneração dos dados sintéticos?', false)) {
            $this->warn('Operação cancelada.');

            return self::FAILURE;
        }

        foreach ($intervencoes as $intervencao) {
            $generator->regenerar($intervencao);
            $this->line("  ✓ Regenerada intervenção #{$intervencao->id}");
        }

        $this->info('Regeneração concluída.');

        return self::SUCCESS;
    }
}
