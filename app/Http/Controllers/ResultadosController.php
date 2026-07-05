<?php

namespace App\Http\Controllers;

use App\Services\ResultadosAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultadosController extends Controller
{
    public function __construct(
        private readonly ResultadosAggregatorService $aggregator,
    ) {}

    public function index(Request $request): View
    {
        $userId = (int) auth()->id();
        $data = $this->aggregator->dashboard($userId);

        $selectedTurma = $request->query('turma');
        $serverAlunos = null;
        $serverIntervencoes = null;

        $interpretacaoTurma = null;

        $serverTurmaMetrics = null;

        if ($selectedTurma) {
            $serverAlunos = $this->aggregator->alunosPorTurma($userId, $selectedTurma);
            $serverIntervencoes = $this->aggregator->intervencoesPorTurma($userId, $selectedTurma);
            $serverTurmaMetrics = $this->aggregator->turmaStats($userId, $selectedTurma);
            $interpretacaoTurma = $this->aggregator->interpretacaoTurma($userId, $selectedTurma);
        }

        return view('intervencoes.resultados', [
            'turmasStats' => $data['turmasStats'],
            'intervencoes' => $data['intervencoes'],
            'progressao' => $data['progressao'],
            'selectedTurma' => $selectedTurma,
            'serverAlunos' => $serverAlunos,
            'serverIntervencoes' => $serverIntervencoes,
            'serverTurmaMetrics' => $serverTurmaMetrics,
            'interpretacaoTurma' => $interpretacaoTurma,
        ]);
    }

    public function getTurmaStats(Request $request, string $turma): JsonResponse
    {
        $userId = (int) auth()->id();
        $filtro = $this->parseIntervencaoFiltro($request->query('intervencao'));
        $stats = $this->aggregator->turmaStats($userId, $turma, $filtro);
        $interpretacao = $this->aggregator->interpretacaoTurma($userId, $turma, $filtro);

        return response()->json(array_merge($stats, [
            'interpretacao' => $interpretacao ?? [
                'turma' => $turma,
                'mensagem' => 'Sem dados para interpretação.',
            ],
        ]));
    }

    public function getAlunosByTurma(string $turma): JsonResponse
    {
        return response()->json(
            $this->aggregator->alunosPorTurma((int) auth()->id(), $turma)
        );
    }

    public function getIntervencoesByTurma(string $turma): JsonResponse
    {
        return response()->json(
            $this->aggregator->intervencoesPorTurma((int) auth()->id(), $turma)
        );
    }

    public function getInterpretacaoTurma(Request $request, string $turma): JsonResponse
    {
        $userId = (int) auth()->id();
        $filtro = $this->parseIntervencaoFiltro($request->query('intervencao'));

        $interpretacao = $this->aggregator->interpretacaoTurma($userId, $turma, $filtro);

        return response()->json($interpretacao ?? ['turma' => $turma, 'mensagem' => 'Sem dados para interpretação.']);
    }

    public function getIntervencaoAnalise(string $turma, int $intervencao): JsonResponse
    {
        $analise = $this->aggregator->analiseIntervencao((int) auth()->id(), $turma, $intervencao);

        if ($analise === null) {
            return response()->json(['mensagem' => 'Intervenção não encontrada nesta turma.'], 404);
        }

        return response()->json($analise);
    }

    private function parseIntervencaoFiltro(mixed $intervencaoId): ?int
    {
        if ($intervencaoId === null || $intervencaoId === '' || $intervencaoId === 'all') {
            return null;
        }

        $parsed = filter_var($intervencaoId, FILTER_VALIDATE_INT);

        return $parsed !== false && $parsed > 0 ? $parsed : null;
    }
}
