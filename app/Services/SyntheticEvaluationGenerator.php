<?php

namespace App\Services;

use App\Data\SyntheticDatasetRepository;
use App\Enums\Cenario;
use App\Models\Avaliacao;
use App\Models\Intervencao;
use App\Support\ResultadosCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyntheticEvaluationGenerator
{
    public function __construct(
        private readonly SyntheticDatasetRepository $datasets,
    ) {}

    public function jaGerado(Intervencao $intervencao): bool
    {
        if ($intervencao->dados_gerados_at !== null) {
            return true;
        }

        return Avaliacao::where('intervencao_id', $intervencao->id)
            ->where('tipo', 'pos')
            ->exists();
    }

    /**
     * @throws ValidationException
     */
    public function gerar(Intervencao $intervencao): void
    {
        if ($this->jaGerado($intervencao)) {
            throw ValidationException::withMessages([
                'cenario' => 'Os dados desta intervenção já foram gerados.',
            ]);
        }

        $cenario = Cenario::fromInput((string) $intervencao->cenario)->value;
        $dataset = $this->datasets->forTurma((string) $intervencao->turma, $cenario);

        DB::transaction(function () use ($intervencao, $cenario, $dataset): void {
            foreach ($dataset as $idx => $aluno) {
                $numero = $idx + 1;
                $nome = $aluno['nome'];
                $pre = $aluno['pre'];
                $posData = $aluno['pos'];

                $preInicio = $pre['temporalidade_inicio'];
                $preFim = $pre['temporalidade_fim'];

                Avaliacao::create([
                    'intervencao_id' => $intervencao->id,
                    'cenario' => $cenario,
                    'aluno_numero' => $numero,
                    'aluno_nome' => $nome,
                    'tipo' => 'pre',
                    'adesao' => 0,
                    'aderencia' => $pre['aderencia'],
                    'temporalidade_inicio' => $preInicio,
                    'temporalidade_fim' => $preFim,
                    'temporalidade' => (int) round(($preInicio + $preFim) / 2),
                    'desempenho' => $pre['desempenho'],
                    'observacoes' => 'Dataset fixo -- pre-intervencao',
                ]);

                $posInicio = $posData['temporalidade_inicio'];
                $posFim = $posData['temporalidade_fim'];

                Avaliacao::create([
                    'intervencao_id' => $intervencao->id,
                    'cenario' => $cenario,
                    'aluno_numero' => $numero,
                    'aluno_nome' => $nome,
                    'tipo' => 'pos',
                    'adesao' => $posData['adesao'],
                    'aderencia' => $posData['aderencia'],
                    'temporalidade_inicio' => $posInicio,
                    'temporalidade_fim' => $posFim,
                    'temporalidade' => (int) round(($posInicio + $posFim) / 2),
                    'desempenho' => $posData['desempenho'],
                    'observacoes' => $posData['adesao']
                        ? 'Dataset fixo -- pos-intervencao'
                        : 'Dataset fixo -- nao aderiu',
                ]);
            }

            $intervencao->update(['dados_gerados_at' => now()]);
        });

        ResultadosCache::forgetUser((int) $intervencao->user_id);
    }

    public function limpar(Intervencao $intervencao): void
    {
        DB::transaction(function () use ($intervencao): void {
            Avaliacao::where('intervencao_id', $intervencao->id)->delete();
            $intervencao->update(['dados_gerados_at' => null]);
        });

        ResultadosCache::forgetUser((int) $intervencao->user_id);
    }

    /**
     * @throws ValidationException
     */
    public function regenerar(Intervencao $intervencao): void
    {
        $this->limpar($intervencao);
        $this->gerar($intervencao->fresh());
    }
}
