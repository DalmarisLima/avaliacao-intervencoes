<?php

namespace App\Http\Controllers;

use App\Enums\Cenario;
use App\Http\Requests\SalvarCenarioRequest;
use App\Jobs\GenerateSyntheticEvaluationsJob;
use App\Models\EstudoConfiguracao;
use App\Models\Intervencao;
use App\Services\IntervencaoSetupService;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IntervencaoController extends Controller
{
    public function __construct(
        private readonly SyntheticEvaluationGenerator $evaluationGenerator,
        private readonly IntervencaoSetupService $setup,
    ) {}

    public function index(): View
    {
        $intervencoes = Intervencao::query()
            ->withCount([
                'avaliacoes',
                'avaliacoes as avaliacoes_pos_count' => fn ($q) => $q->where('tipo', 'pos'),
            ])
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->paginate(10);

        $semIntervencoes = $intervencoes->total() === 0;

        return view('intervencoes.index', compact('intervencoes', 'semIntervencoes'));
    }

    public function create(): View
    {
        $estudo = EstudoConfiguracao::obter();
        $cenarioAtual = Cenario::Flexivel;

        return view('intervencoes.create', [
            'estudo' => $estudo,
            'cenarioAtual' => $cenarioAtual,
            'conteudoIntervencao' => $estudo->conteudoIntervencaoPara($cenarioAtual),
        ]);
    }

    public function iniciarCenario(): RedirectResponse
    {
        $intervencao = $this->setup->criarParaUsuario(auth()->user());

        return redirect()
            ->route('intervencoes.definir-cenario', ['intervencao' => $intervencao->id])
            ->with('info', 'Leia os limiares sugeridos e confirme o cenário de avaliação.');
    }

    public function definirCenario(Intervencao $intervencao): View|RedirectResponse
    {
        $this->authorize('view', $intervencao);

        if ($this->evaluationGenerator->jaGerado($intervencao)) {
            return redirect()
                ->route('resultados', ['turma' => $intervencao->turma])
                ->with('warning', 'Esta intervenção já foi processada. Resultados disponíveis abaixo.');
        }

        $cenarioSugerido = $intervencao->cenario;

        return view('intervencoes.definir-cenario', compact('intervencao', 'cenarioSugerido'));
    }

    public function salvarCenario(SalvarCenarioRequest $request, Intervencao $intervencao): RedirectResponse
    {
        $this->authorize('update', $intervencao);

        $data = $request->validated();
        $cenario = Cenario::fromInput((string) $data['cenario']);

        $intervencao->update([
            'cenario' => $cenario->value,
            'limiar_aderencia' => (int) $data['limiar_aderencia'],
            'limiar_temporalidade_inicio' => (int) $data['limiar_temporalidade_inicio'],
            'limiar_temporalidade_fim' => (int) $data['limiar_temporalidade_fim'],
            'limiar_desempenho' => (int) $data['limiar_desempenho'],
        ]);

        $queued = config('resultados.queue_generation');

        try {
            if ($queued) {
                GenerateSyntheticEvaluationsJob::dispatch($intervencao->id);
            } else {
                $this->evaluationGenerator->gerar($intervencao);
            }
        } catch (ValidationException $e) {
            return redirect()
                ->route('resultados', ['turma' => $intervencao->turma])
                ->with('warning', $e->getMessage());
        }

        return redirect()
            ->route('resultados', ['turma' => $intervencao->turma])
            ->with(
                'success',
                $queued
                    ? 'Cenário salvo. Os dados estão sendo gerados em segundo plano.'
                    : 'Cenário configurado com sucesso. Veja os resultados abaixo.'
            );
    }
}
