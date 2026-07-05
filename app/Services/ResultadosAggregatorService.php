<?php

namespace App\Services;

use App\Enums\Cenario;
use App\Models\Intervencao;
use App\Support\ResultadosCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResultadosAggregatorService
{
    public function __construct(
        private readonly CenarioService $cenarioService,
        private readonly EficaciaInterpretacaoService $interpretacaoService,
        private readonly IntervencaoProfessorInsightsService $professorInsights,
        private readonly TurmaProgressoMetricasService $progressoMetricas,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(int $userId): array
    {
        $ttl = config('resultados.cache_ttl', 3600);

        return Cache::remember(
            ResultadosCache::indexKey($userId),
            $ttl,
            fn () => $this->buildDashboard($userId)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function turmaStats(int $userId, string $turma, ?int $intervencaoId = null): array
    {
        $ttl = config('resultados.cache_ttl', 3600);
        $suffix = $intervencaoId ? 'stats_i'.$intervencaoId : 'stats';

        return Cache::remember(
            ResultadosCache::turmaKey($userId, $turma, $suffix),
            $ttl,
            fn () => $this->buildTurmaStats($userId, $turma, $intervencaoId)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function alunosPorTurma(int $userId, string $turma): array
    {
        $ttl = config('resultados.cache_ttl', 3600);

        return Cache::remember(
            ResultadosCache::turmaKey($userId, $turma, 'alunos'),
            $ttl,
            fn () => $this->buildAlunosPorTurma($userId, $turma)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function intervencoesPorTurma(int $userId, string $turma): array
    {
        $ttl = config('resultados.cache_ttl', 3600);

        return Cache::remember(
            ResultadosCache::turmaKey($userId, $turma, 'intervencoes'),
            $ttl,
            fn () => $this->buildIntervencoesPorTurma($userId, $turma)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboard(int $userId): array
    {
        $turmasStats = DB::table('intervencoes')
            ->join('avaliacoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->selectRaw(
                'intervencoes.turma,
                COUNT(DISTINCT intervencoes.id) as total_intervencoes,
                SUM(avaliacoes.adesao) as adesao_count,
                COUNT(avaliacoes.id) as total_avaliacoes,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.aderencia ELSE 0 END) as aderencia_media,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_inicio ELSE 0 END) as temporalidade_inicio_media,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_fim ELSE 0 END) as temporalidade_fim_media,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN (avaliacoes.temporalidade_inicio + avaliacoes.temporalidade_fim) / 2 ELSE 0 END) as temporalidade_media,
                AVG(CASE WHEN avaliacoes.adesao = 1 THEN avaliacoes.desempenho ELSE 0 END) as desempenho_media'
            )
            ->where('avaliacoes.tipo', 'pos')
            ->where('intervencoes.user_id', $userId)
            ->whereNotNull('intervencoes.turma')
            ->groupBy('intervencoes.turma')
            ->get();

        $turmasStatsPre = DB::table('intervencoes')
            ->join('avaliacoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->selectRaw(
                'intervencoes.turma,
                AVG(avaliacoes.aderencia) as aderencia_media,
                AVG(avaliacoes.temporalidade_inicio) as temporalidade_inicio_media,
                AVG(avaliacoes.temporalidade_fim) as temporalidade_fim_media,
                AVG((avaliacoes.temporalidade_inicio + avaliacoes.temporalidade_fim) / 2) as temporalidade_media,
                AVG(avaliacoes.desempenho) as desempenho_media'
            )
            ->where('avaliacoes.tipo', 'pre')
            ->where('intervencoes.user_id', $userId)
            ->whereNotNull('intervencoes.turma')
            ->groupBy('intervencoes.turma')
            ->get()
            ->keyBy('turma');

        foreach ($turmasStats as $stat) {
            $preStat = $turmasStatsPre->get($stat->turma);
            $stat->pre_aderencia = $preStat->aderencia_media ?? 0;
            $stat->pre_temporalidade_inicio = $preStat->temporalidade_inicio_media ?? 0;
            $stat->pre_temporalidade_fim = $preStat->temporalidade_fim_media ?? 0;
            $stat->pre_temporalidade = $preStat->temporalidade_media ?? 0;

            $metricasTurma = $this->progressoMetricas->fromAlunosPayload(
                $this->buildAlunosPorTurma($userId, (string) $stat->turma)
            );
            $stat->pre_desempenho = $metricasTurma['pre']['desempenho'];
            $stat->desempenho_media = $metricasTurma['pos']['desempenho'];
        }

        $intervencoes = Intervencao::query()
            ->withCount([
                'avaliacoes',
                'avaliacoes as avaliacoes_pos_count' => fn ($q) => $q->where('tipo', 'pos'),
                'avaliacoes as avaliacoes_pre_count' => fn ($q) => $q->where('tipo', 'pre'),
            ])
            ->where('user_id', $userId)
            ->whereHas('avaliacoes', fn ($q) => $q->where('tipo', 'pos'))
            ->get();

        return [
            'turmasStats' => $turmasStats,
            'intervencoes' => $intervencoes,
            'progressao' => $this->buildProgressao($intervencoes),
        ];
    }

    /**
     * @param  Collection<int, Intervencao>  $intervencoes
     * @return list<array<string, mixed>>
     */
    private function buildProgressao(Collection $intervencoes): array
    {
        if ($intervencoes->isEmpty()) {
            return [];
        }

        $ids = $intervencoes->pluck('id')->all();

        $preRows = DB::table('avaliacoes')
            ->whereIn('intervencao_id', $ids)
            ->where('tipo', 'pre')
            ->selectRaw('
                intervencao_id,
                AVG(aderencia) as aderencia_mean,
                AVG(temporalidade_inicio) as temporalidade_inicio_mean,
                AVG(temporalidade_fim) as temporalidade_fim_mean,
                AVG((temporalidade_inicio + temporalidade_fim) / 2) as temporalidade_mean,
                AVG(desempenho) as desempenho_mean
            ')
            ->groupBy('intervencao_id')
            ->get()
            ->keyBy('intervencao_id');

        $posRows = DB::table('avaliacoes')
            ->whereIn('intervencao_id', $ids)
            ->where('tipo', 'pos')
            ->selectRaw('
                intervencao_id,
                (CAST(SUM(adesao) AS REAL) / COUNT(id) * 100) as adesao_percentual,
                SUM(adesao) as adesao_count,
                COUNT(id) as total_pos_count,
                AVG(CASE WHEN adesao = 1 THEN aderencia ELSE 0 END) as aderencia_mean,
                AVG(CASE WHEN adesao = 1 THEN temporalidade_inicio ELSE 0 END) as temporalidade_inicio_mean,
                AVG(CASE WHEN adesao = 1 THEN temporalidade_fim ELSE 0 END) as temporalidade_fim_mean,
                AVG(CASE WHEN adesao = 1 THEN (temporalidade_inicio + temporalidade_fim) / 2 ELSE 0 END) as temporalidade_mean,
                AVG(CASE WHEN adesao = 1 THEN desempenho ELSE 0 END) as desempenho_mean
            ')
            ->groupBy('intervencao_id')
            ->get()
            ->keyBy('intervencao_id');

        $progressao = [];

        foreach ($intervencoes as $interv) {
            $preMean = $preRows->get($interv->id);
            $posMean = $posRows->get($interv->id);
            $adesaoCount = (int) ($posMean->adesao_count ?? 0);
            $totalPosCount = (int) ($posMean->total_pos_count ?? 0);
            $temAdesao = $adesaoCount > 0;

            $progressao[] = [
                'titulo' => $interv->titulo,
                'turma' => $interv->turma,
                'id' => $interv->id,
                'aderido' => $temAdesao,
                'adesao_status' => $temAdesao ? 'Sim' : 'Não',
                'adesao_count' => $adesaoCount,
                'adesao_total' => $totalPosCount,
                'antes' => [
                    'aderencia' => round($preMean->aderencia_mean ?? 0),
                    'temporalidade_inicio' => round($preMean->temporalidade_inicio_mean ?? 0),
                    'temporalidade_fim' => round($preMean->temporalidade_fim_mean ?? 0),
                    'temporalidade' => round($preMean->temporalidade_mean ?? 0),
                    'desempenho' => round($preMean->desempenho_mean ?? 0),
                ],
                'depois' => [
                    'adesao_percentual' => round($posMean->adesao_percentual ?? 0),
                    'aderencia' => round($posMean->aderencia_mean ?? 0),
                    'temporalidade_inicio' => round($posMean->temporalidade_inicio_mean ?? 0),
                    'temporalidade_fim' => round($posMean->temporalidade_fim_mean ?? 0),
                    'temporalidade' => round($posMean->temporalidade_mean ?? 0),
                    'desempenho' => round($posMean->desempenho_mean ?? 0),
                ],
            ];
        }

        return $progressao;
    }

    /**
     * @return array{pre: array<string, mixed>, pos: array<string, mixed>, ganhos: array<string, mixed>}
     */
    private function buildTurmaStats(int $userId, string $turma, ?int $intervencaoId = null): array
    {
        $alunos = $this->buildAlunosPorTurma($userId, $turma);
        $metricas = $this->progressoMetricas->fromAlunosPayload($alunos, $intervencaoId);

        return [
            'pre' => $metricas['pre'],
            'pos' => $metricas['pos'],
            'ganhos' => $metricas['ganhos'],
            'total_pares_aderentes' => $metricas['total_pares_aderentes'],
        ];
    }

    /**
     * @return array{turma: string, alunos: list<array<string, mixed>>}
     */
    private function buildAlunosPorTurma(int $userId, string $turma): array
    {
        $avaliacoes = DB::table('avaliacoes')
            ->join('intervencoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->selectRaw('
                avaliacoes.aluno_numero,
                avaliacoes.aluno_nome,
                avaliacoes.cenario,
                intervencoes.titulo,
                intervencoes.id as intervencao_id,
                intervencoes.turma,
                intervencoes.limiar_aderencia,
                intervencoes.limiar_temporalidade_inicio,
                intervencoes.limiar_temporalidade_fim,
                intervencoes.limiar_desempenho,
                avaliacoes.tipo,
                avaliacoes.adesao,
                avaliacoes.aderencia,
                avaliacoes.temporalidade_inicio,
                avaliacoes.temporalidade_fim,
                avaliacoes.temporalidade,
                avaliacoes.desempenho
            ')
            ->where('intervencoes.turma', $turma)
            ->where('intervencoes.user_id', $userId)
            ->orderBy('avaliacoes.aluno_numero')
            ->orderBy('intervencoes.id')
            ->orderBy('avaliacoes.tipo')
            ->get();

        $alunosPorTurma = [];

        foreach ($avaliacoes as $avaliacao) {
            $alunoKey = (string) $avaliacao->aluno_numero;

            if (! isset($alunosPorTurma[$alunoKey])) {
                $alunosPorTurma[$alunoKey] = [
                    'aluno_numero' => $avaliacao->aluno_numero,
                    'aluno_nome' => $avaliacao->aluno_nome,
                    'intervencoes' => [],
                ];
            } elseif (empty($alunosPorTurma[$alunoKey]['aluno_nome']) && ! empty($avaliacao->aluno_nome)) {
                $alunosPorTurma[$alunoKey]['aluno_nome'] = $avaliacao->aluno_nome;
            }

            $intervencaoKey = $avaliacao->intervencao_id.'_'.$avaliacao->titulo;

            if (! isset($alunosPorTurma[$alunoKey]['intervencoes'][$intervencaoKey])) {
                $alunosPorTurma[$alunoKey]['intervencoes'][$intervencaoKey] = [
                    'intervencao_id' => $avaliacao->intervencao_id,
                    'titulo' => $avaliacao->titulo,
                    'cenario' => null,
                    'limiar_aderencia' => $avaliacao->limiar_aderencia ?? 25,
                    'limiar_temporalidade_inicio' => $avaliacao->limiar_temporalidade_inicio ?? 20,
                    'limiar_temporalidade_fim' => $avaliacao->limiar_temporalidade_fim ?? 60,
                    'limiar_desempenho' => $avaliacao->limiar_desempenho ?? 25,
                    'pre' => null,
                    'pos' => null,
                ];
            }

            if (! empty($avaliacao->cenario)) {
                $alunosPorTurma[$alunoKey]['intervencoes'][$intervencaoKey]['cenario'] = $this->cenarioService->rotulo($avaliacao->cenario);
            }

            if ($avaliacao->tipo === 'pre') {
                $alunosPorTurma[$alunoKey]['intervencoes'][$intervencaoKey]['pre'] = [
                    'aderencia' => $avaliacao->aderencia,
                    'temporalidade_inicio' => $avaliacao->temporalidade_inicio,
                    'temporalidade_fim' => $avaliacao->temporalidade_fim,
                    'temporalidade' => $avaliacao->temporalidade,
                    'desempenho' => $avaliacao->desempenho,
                ];
            } else {
                $alunosPorTurma[$alunoKey]['intervencoes'][$intervencaoKey]['pos'] = [
                    'adesao' => $avaliacao->adesao ? 'Sim' : 'Não',
                    'aderencia' => $avaliacao->aderencia,
                    'temporalidade_inicio' => $avaliacao->temporalidade_inicio,
                    'temporalidade_fim' => $avaliacao->temporalidade_fim,
                    'temporalidade' => $avaliacao->temporalidade,
                    'desempenho' => $avaliacao->desempenho,
                ];
            }
        }

        foreach ($alunosPorTurma as &$aluno) {
            $aluno['intervencoes'] = array_values($aluno['intervencoes']);
        }

        return [
            'turma' => $turma,
            'alunos' => array_values($alunosPorTurma),
        ];
    }

    /**
     * @return array{turma: string, intervencoes: list<array<string, mixed>>}
     */
    private function buildIntervencoesPorTurma(int $userId, string $turma): array
    {
        $totalAlunosTurma = DB::table('avaliacoes')
            ->join('intervencoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->where('intervencoes.turma', $turma)
            ->where('intervencoes.user_id', $userId)
            ->where('avaliacoes.tipo', 'pre')
            ->distinct('avaliacoes.aluno_numero')
            ->count('avaliacoes.aluno_numero');

        $intervencoes = DB::table('intervencoes')
            ->leftJoin('avaliacoes', 'intervencoes.id', '=', 'avaliacoes.intervencao_id')
            ->selectRaw('
                intervencoes.id,
                intervencoes.titulo,
                intervencoes.turma,
                intervencoes.limiar_aderencia,
                intervencoes.limiar_temporalidade_inicio,
                intervencoes.limiar_temporalidade_fim,
                intervencoes.limiar_desempenho,
                COALESCE(
                    MAX(CASE WHEN avaliacoes.tipo = "pos" THEN NULLIF(avaliacoes.cenario, "") ELSE NULL END),
                    MAX(CASE WHEN avaliacoes.tipo = "pre" THEN NULLIF(avaliacoes.cenario, "") ELSE NULL END)
                ) as cenario,
                SUM(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN 1 ELSE 0 END) as adesao_count,
                AVG(CASE WHEN avaliacoes.tipo = "pre" THEN avaliacoes.aderencia ELSE NULL END) as pre_aderencia,
                AVG(CASE WHEN avaliacoes.tipo = "pre" THEN avaliacoes.temporalidade_inicio ELSE NULL END) as pre_temporalidade_inicio,
                AVG(CASE WHEN avaliacoes.tipo = "pre" THEN avaliacoes.temporalidade_fim ELSE NULL END) as pre_temporalidade_fim,
                AVG(CASE WHEN avaliacoes.tipo = "pre" THEN (avaliacoes.temporalidade_inicio + avaliacoes.temporalidade_fim) / 2 ELSE NULL END) as pre_temporalidade,
                AVG(CASE WHEN avaliacoes.tipo = "pre" THEN avaliacoes.desempenho ELSE NULL END) as pre_desempenho,
                AVG(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN avaliacoes.aderencia ELSE NULL END) as pos_aderencia,
                AVG(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_inicio ELSE NULL END) as pos_temporalidade_inicio,
                AVG(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN avaliacoes.temporalidade_fim ELSE NULL END) as pos_temporalidade_fim,
                AVG(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN (avaliacoes.temporalidade_inicio + avaliacoes.temporalidade_fim) / 2 ELSE NULL END) as pos_temporalidade,
                AVG(CASE WHEN avaliacoes.tipo = "pos" AND avaliacoes.adesao = 1 THEN avaliacoes.desempenho ELSE NULL END) as pos_desempenho
            ')
            ->where('intervencoes.turma', $turma)
            ->where('intervencoes.user_id', $userId)
            ->groupBy(
                'intervencoes.id',
                'intervencoes.titulo',
                'intervencoes.turma',
                'intervencoes.limiar_aderencia',
                'intervencoes.limiar_temporalidade_inicio',
                'intervencoes.limiar_temporalidade_fim',
                'intervencoes.limiar_desempenho'
            )
            ->orderBy('intervencoes.id')
            ->get();

        $intervencoesPorTurma = [];

        foreach ($intervencoes as $item) {
            $key = $item->id.'_'.$item->titulo;
            $cenarioPerfil = $this->cenarioService->perfil($item->cenario);
            $adesaoPercentual = (int) ($item->adesao_count ?? 0) > 0 && $totalAlunosTurma > 0
                ? round(((int) ($item->adesao_count ?? 0) / $totalAlunosTurma) * 100)
                : 0;

            $avaliacaoEficacia = $this->cenarioService->avaliarEficacia($item->cenario, [
                'adesao_percentual' => $adesaoPercentual,
                'aderencia' => round($item->pos_aderencia ?? 0),
                'temporalidade_inicio' => round($item->pos_temporalidade_inicio ?? 0),
                'temporalidade_fim' => round($item->pos_temporalidade_fim ?? 0),
                'desempenho' => round($item->pos_desempenho ?? 0),
                'pre_desempenho' => round($item->pre_desempenho ?? 0),
            ], [
                'aderencia' => (int) ($item->limiar_aderencia ?? 25),
                'temporalidade_inicio' => (int) ($item->limiar_temporalidade_inicio ?? 20),
                'temporalidade_fim' => (int) ($item->limiar_temporalidade_fim ?? 60),
                'desempenho' => (int) ($item->limiar_desempenho ?? 25),
            ]);

            if (! isset($intervencoesPorTurma[$key])) {
                $intervencoesPorTurma[$key] = [
                    'id' => $item->id,
                    'titulo' => $item->titulo,
                    'titulo_exibicao' => Cenario::tituloTabela(
                        $this->cenarioService->normalizar($item->cenario),
                        $item->titulo
                    ),
                    'turma' => $item->turma,
                    'cenario' => $this->cenarioService->rotulo($item->cenario),
                    'cenario_normalizado' => $this->cenarioService->normalizar($item->cenario),
                    'cenario_perfil' => $cenarioPerfil,
                    'limiar_aderencia' => (int) ($item->limiar_aderencia ?? 25),
                    'limiar_temporalidade_inicio' => (int) ($item->limiar_temporalidade_inicio ?? 20),
                    'limiar_temporalidade_fim' => (int) ($item->limiar_temporalidade_fim ?? 60),
                    'limiar_desempenho' => (int) ($item->limiar_desempenho ?? 25),
                    'avaliacao_eficacia' => $avaliacaoEficacia,
                    'pre' => null,
                    'pos' => null,
                ];
            }

            $intervencoesPorTurma[$key]['pre'] = [
                'aderencia' => round($item->pre_aderencia ?? 0),
                'temporalidade_inicio' => round($item->pre_temporalidade_inicio ?? 0),
                'temporalidade_fim' => round($item->pre_temporalidade_fim ?? 0),
                'temporalidade' => round($item->pre_temporalidade ?? 0),
                'desempenho' => round($item->pre_desempenho ?? 0),
            ];

            $intervencoesPorTurma[$key]['pos'] = [
                'adesao' => (int) ($item->adesao_count ?? 0).'/'.(int) ($totalAlunosTurma ?: 0),
                'aderencia' => round($item->pos_aderencia ?? 0),
                'temporalidade_inicio' => round($item->pos_temporalidade_inicio ?? 0),
                'temporalidade_fim' => round($item->pos_temporalidade_fim ?? 0),
                'temporalidade' => round($item->pos_temporalidade ?? 0),
                'desempenho' => round($item->pos_desempenho ?? 0),
            ];
        }

        $payload = [
            'turma' => $turma,
            'intervencoes' => array_values($intervencoesPorTurma),
        ];

        $alunos = $this->buildAlunosPorTurma($userId, $turma);
        $payload = $this->progressoMetricas->aplicarPreEntreAderentesNasIntervencoes($payload, $alunos);

        return $this->recalcularAvaliacaoEficaciaIntervencoes($payload, $totalAlunosTurma);
    }

    /**
     * @param  array{turma: string, intervencoes: list<array<string, mixed>>}  $payload
     * @return array{turma: string, intervencoes: list<array<string, mixed>>}
     */
    private function recalcularAvaliacaoEficaciaIntervencoes(array $payload, int $totalAlunosTurma): array
    {
        $lista = $payload['intervencoes'];
        foreach ($lista as $indice => $item) {
            $adesaoStr = $item['pos']['adesao'] ?? '0/0';
            $parts = explode('/', (string) $adesaoStr);
            $adesaoPercentual = $totalAlunosTurma > 0
                ? (int) round(((int) ($parts[0] ?? 0) / $totalAlunosTurma) * 100)
                : 0;

            $lista[$indice]['avaliacao_eficacia'] = $this->cenarioService->avaliarEficacia(
                $item['cenario_normalizado'] ?? $item['cenario'] ?? null,
                [
                    'adesao_percentual' => $adesaoPercentual,
                    'aderencia' => (int) ($item['pos']['aderencia'] ?? 0),
                    'temporalidade_inicio' => (int) ($item['pos']['temporalidade_inicio'] ?? 0),
                    'temporalidade_fim' => (int) ($item['pos']['temporalidade_fim'] ?? 0),
                    'desempenho' => (int) ($item['pos']['desempenho'] ?? 0),
                    'pre_desempenho' => (int) ($item['pre']['desempenho'] ?? -1),
                ],
                [
                    'aderencia' => (int) ($item['limiar_aderencia'] ?? 25),
                    'temporalidade_inicio' => (int) ($item['limiar_temporalidade_inicio'] ?? 20),
                    'temporalidade_fim' => (int) ($item['limiar_temporalidade_fim'] ?? 60),
                    'desempenho' => (int) ($item['limiar_desempenho'] ?? 25),
                ]
            );
        }
        $payload['intervencoes'] = $lista;

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function interpretacaoTurma(int $userId, string $turma, ?int $intervencaoId = null): ?array
    {
        $stats = $this->turmaStats($userId, $turma, $intervencaoId);
        $dados = $this->intervencoesPorTurma($userId, $turma);

        $intervencoes = $dados['intervencoes'];
        if ($intervencaoId !== null) {
            $intervencoes = array_values(array_filter(
                $intervencoes,
                fn (array $item): bool => (int) ($item['id'] ?? 0) === $intervencaoId
            ));
        }

        if (count($intervencoes) === 0) {
            return null;
        }

        $mediaPre = (int) ($stats['pre']['desempenho'] ?? 0);
        $mediaPos = (int) ($stats['pos']['desempenho'] ?? 0);

        $interpretacao = $this->interpretacaoService->interpretarTurma(
            $turma,
            $intervencoes,
            $mediaPre,
            $mediaPos
        );

        return $this->professorInsights->enriquecerInterpretacaoTurma($interpretacao);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function analiseIntervencao(int $userId, string $turma, int $intervencaoId): ?array
    {
        $dados = $this->intervencoesPorTurma($userId, $turma);
        $totalAlunos = $this->totalAlunosTurma($userId, $turma);

        foreach ($dados['intervencoes'] as $item) {
            if ((int) ($item['id'] ?? 0) === $intervencaoId) {
                return $this->professorInsights->analisar($item, $totalAlunos);
            }
        }

        return null;
    }

    private function totalAlunosTurma(int $userId, string $turma): int
    {
        $alunos = $this->alunosPorTurma($userId, $turma);

        return count($alunos['alunos'] ?? []);
    }
}
