<?php

namespace App\Http\Controllers;

use App\Enums\Cenario;
use App\Http\Requests\StoreAvaliacaoRequest;
use App\Models\Avaliacao;
use App\Models\Intervencao;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AvaliacaoController extends Controller
{
    public function __construct(
        private readonly SyntheticEvaluationGenerator $evaluationGenerator,
    ) {}

    public function create(Intervencao $intervencao): View|RedirectResponse
    {
        $this->authorize('view', $intervencao);

        if ($this->evaluationGenerator->jaGerado($intervencao)) {
            return redirect()
                ->route('resultados', ['turma' => $intervencao->turma])
                ->with('warning', 'Esta intervenção usa dados sintéticos gerados automaticamente. Avaliação manual não está disponível.');
        }

        return view('intervencoes.avaliacao', compact('intervencao'));
    }

    public function store(StoreAvaliacaoRequest $request, Intervencao $intervencao): RedirectResponse
    {
        $this->authorize('update', $intervencao);

        if ($this->evaluationGenerator->jaGerado($intervencao)) {
            return redirect()
                ->route('resultados', ['turma' => $intervencao->turma])
                ->with('warning', 'Não é possível adicionar avaliação manual após a geração automática dos dados.');
        }

        $data = $request->validated();
        $cenario = Cenario::fromInput((string) $data['cenario']);
        $adesaoValue = $data['adesao'] === 'sim' ? 1 : 0;
        $temporalidadeInicio = (int) $data['temporalidade_inicio'];
        $temporalidadeFim = (int) $data['temporalidade_fim'];

        Avaliacao::create([
            'intervencao_id' => $intervencao->id,
            'cenario' => $cenario->value,
            'tipo' => 'pos',
            'aluno_numero' => 1,
            'aluno_nome' => 'Avaliação manual',
            'adesao' => $adesaoValue,
            'aderencia' => $data['aderencia'],
            'temporalidade' => (int) round(($temporalidadeInicio + $temporalidadeFim) / 2),
            'temporalidade_inicio' => $temporalidadeInicio,
            'temporalidade_fim' => $temporalidadeFim,
            'desempenho' => $data['desempenho'],
            'observacoes' => $data['observacoes'] ?? 'Avaliação manual única',
        ]);

        return redirect()
            ->route('resultados', ['turma' => $intervencao->turma])
            ->with('success', 'Avaliação salva com sucesso');
    }
}
