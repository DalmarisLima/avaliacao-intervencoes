<?php

namespace App\Jobs;

use App\Models\Intervencao;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSyntheticEvaluationsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $intervencaoId,
    ) {}

    public function handle(SyntheticEvaluationGenerator $generator): void
    {
        $intervencao = Intervencao::findOrFail($this->intervencaoId);
        $generator->gerar($intervencao);
    }
}
