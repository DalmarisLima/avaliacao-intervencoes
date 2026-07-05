@extends('layouts.app')

@section('title', 'Resultados')

    @push('styles')
    @php
        $chartsCssV = @filemtime(public_path('css/charts.css')) ?: time();
    @endphp
    <link rel="stylesheet" href="{{ asset('css/charts.css') }}?v={{ $chartsCssV }}">
    @endpush

@section('content')
<div class="content-container">
    @if(session('success'))
        <x-wizard-steps :current="3" />
    @endif

    <div class="page-title-block">
        <h1 class="page-title">Resultados das intervenções</h1>
        <p class="page-subtitle">Análise de desempenho e eficácia das intervenções por turma.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning" role="alert">
        {{ session('warning') }}
    </div>
    @endif

    @if(($turmasStats->count() ?? 0) > 0)
    <div class="analytics-toolbar analytics-panel">
        <div class="analytics-toolbar__inner">
            <div class="analytics-toolbar__field">
                <label for="turmaFiltro" class="form-label">Turma</label>
                <form method="get" action="{{ url()->current() }}" id="turmaFiltroForm" class="analytics-toolbar__form">
                    <select class="form-select" name="turma" id="turmaFiltro">
                        <option value="">Selecione uma turma</option>
                        @foreach($turmasStats as $stats)
                        <option value="{{ $stats->turma }}" {{ (isset($selectedTurma) && $selectedTurma == $stats->turma) ? 'selected' : '' }}>{{ $stats->turma }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="analytics-toolbar__field" id="intervencaoFiltroWrapper" style="{{ isset($selectedTurma) && $selectedTurma ? '' : 'display: none;' }}">
                <label for="intervencaoFiltro" class="form-label">Intervenção</label>
                <select class="form-select" id="intervencaoFiltro">
                    <option value="all" selected>Todas as intervenções</option>
                    @foreach(($serverIntervencoes['intervencoes'] ?? []) as $it)
                        <option value="{{ $it['id'] ?? '' }}">{{ $it['titulo_exibicao'] ?? $it['titulo'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif

    <div id="debugLog" class="debug-log"></div>

    @if(($turmasStats->count() ?? 0) === 0)
        <x-empty-state
            title="Sem dados para análise"
            text="Crie uma intervenção, defina o cenário e aguarde a geração dos dados para visualizar os resultados aqui."
            action-label="Intervenção pedagógica"
            :action-url="route('intervencoes.create')"
            icon="📊"
        />
    @endif

    <div id="resultadosEmptyState" class="results-placeholder" style="{{ (isset($selectedTurma) && $selectedTurma) || ($turmasStats->count() ?? 0) === 0 ? 'display: none;' : '' }}">
        Selecione uma turma no filtro acima para ver a comparação pré/pós, eficácia e detalhes por intervenção e aluno.
    </div>

    @php
    if (!function_exists('interpretarMetrica')) {
        function interpretarMetrica($tipo, $valor) {
            if ($tipo === 'aderencia') {
                if ($valor >= 80) return '✓ Excelente - Tarefas completadas com precisão';
                if ($valor >= 60) return '◐ Bom - Maioria das tarefas completadas';
                if ($valor >= 40) return '⚠ Razoável - Algumas dificuldades na execução';
                return '✗ Insuficiente - Sérias dificuldades na execução';
            }
            if ($tipo === 'temporalidade') {
                if ($valor <= 15) return '✓ Excelente - Início/finalização muito ágeis';
                if ($valor <= 30) return '◐ Bom - Tempo adequado de início/finalização';
                if ($valor <= 60) return '⚠ Razoável - Tempo acima do esperado';
                return '✗ Insuficiente - Tempo muito elevado para concluir';
            }
            if ($tipo === 'desempenho') {
                if ($valor >= 80) return '✓ Excelente - Realização muito acima da média';
                if ($valor >= 60) return '◐ Bom - Realização acima da média';
                if ($valor >= 40) return '⚠ Razoável - Realização dentro da média';
                return '✗ Abaixo da média - Necessita melhorias';
            }
            return '';
        }
    }

    if (!function_exists('classificarTemporalidade')) {
        function classificarTemporalidade($valor) {
            if ($valor <= 15) return 'Muito rápido';
            if ($valor <= 30) return 'Adequado';
            if ($valor <= 60) return 'Lento';
            return 'Muito lento';
        }
    }

    if (!function_exists('rotuloCenario')) {
        function rotuloCenario($cenario) {
            $valor = strtolower(trim((string) $cenario));
            $valor = str_replace(['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'], ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'], $valor);

            return match ($valor) {
                'leve', 'flexivel' => 'Flexível',
                'rigido', 'moderado', 'modelado', 'modelo' => 'Moderado',
                'dificil', 'personalizado' => 'Difícil',
                default => 'Não definido',
            };
        }
    }

    if (!function_exists('perfilCenario')) {
        function perfilCenario($cenario) {
            $valor = strtolower(trim((string) $cenario));
            $valor = str_replace(['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'], ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'], $valor);

            return match ($valor) {
                'leve', 'flexivel' => ['adesao' => 'sim', 'aderencia' => 25, 'temporalidade_inicio' => 20, 'temporalidade_fim' => 60, 'desempenho' => 25],
                'rigido', 'moderado', 'modelado', 'modelo' => ['adesao' => 'sim', 'aderencia' => 60, 'temporalidade_inicio' => 15, 'temporalidade_fim' => 45, 'desempenho' => 60],
                'dificil', 'personalizado' => ['adesao' => 'sim', 'aderencia' => 80, 'temporalidade_inicio' => 10, 'temporalidade_fim' => 30, 'desempenho' => 80],
                default => ['adesao' => 'sim', 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0],
            };
        }
    }

    if (!function_exists('avaliarEficaciaCenario')) {
        function avaliarEficaciaCenario($cenario, array $metricas, array $limiares = []) {
            // Limiares padrão se não fornecidos
            $limiarAderencia = $limiares['aderencia'] ?? 25;
            $limiarTempInicio = $limiares['temporalidade_inicio'] ?? 20;
            $limiarTempFim = $limiares['temporalidade_fim'] ?? 60;
            $limiarDesempenho = $limiares['desempenho'] ?? 25;

            $adesaoPercentual = (int) ($metricas['adesao_percentual'] ?? 0);
            $aderencia        = (int) ($metricas['aderencia'] ?? 0);
            $temporalidadeInicio = (int) ($metricas['temporalidade_inicio'] ?? 0);
            $temporalidadeFim    = (int) ($metricas['temporalidade_fim'] ?? 0);
            $posDesempenho    = (int) ($metricas['desempenho'] ?? 0);
            $preDesempenho    = (int) ($metricas['pre_desempenho'] ?? -1);
            $perfil           = perfilCenario($cenario);
            $cenarioConfigurado = rotuloCenario($cenario);

            // Sem adesão → sem resultado
            if ($adesaoPercentual <= 0) {
                return [
                    'cenario'          => $cenarioConfigurado,
                    'cenario_resultado' => 'Sem participação',
                    'perfil'           => $perfil,
                    'eficaz'           => null,
                    'texto'            => 'Sem participação',
                    'tem_resultado'    => false,
                ];
            }

            // ─── Regra primária: desempenho deve melhorar ────────────────────
            // Se PÓS desempenho ≤ PRÉ desempenho → não eficaz, independente das demais métricas
            $semGanhoDesempenho = $preDesempenho >= 0 && $posDesempenho <= $preDesempenho;

            if ($semGanhoDesempenho) {
                return [
                    'cenario'               => $cenarioConfigurado,
                    'cenario_resultado'     => 'Sem ganho em desempenho',
                    'perfil'                => $perfil,
                    'eficaz'                => false,
                    'tem_resultado'         => true,
                    'texto'                 => 'Não eficaz',
                ];
            }

            // ─── Classificação pelo nível atingido ───────────────────────────
            if ($aderencia >= 80 && $temporalidadeInicio <= 10 && $temporalidadeFim <= 30 && $posDesempenho >= 80) {
                $cenarioResultado = 'Difícil';
            } elseif ($aderencia >= 60 && $temporalidadeInicio <= 15 && $temporalidadeFim <= 45 && $posDesempenho >= 60) {
                $cenarioResultado = 'Moderado';
            } elseif ($aderencia >= $limiarAderencia && $temporalidadeInicio <= $limiarTempInicio && $temporalidadeFim <= $limiarTempFim && $posDesempenho >= $limiarDesempenho) {
                $cenarioResultado = 'Flexível';
            } else {
                $cenarioResultado = 'Abaixo dos critérios';
            }

            $eficaz = match ($cenarioConfigurado) {
                'Flexível'      => in_array($cenarioResultado, ['Flexível', 'Moderado', 'Difícil'], true),
                'Moderado'      => in_array($cenarioResultado, ['Moderado', 'Difícil'], true),
                'Difícil'       => $cenarioResultado === 'Difícil',
                default         => false,
            };

            return [
                'cenario'          => $cenarioConfigurado,
                'cenario_resultado' => $cenarioResultado,
                'perfil'           => $perfil,
                'eficaz'           => $eficaz,
                'tem_resultado'    => true,
                'texto'            => $eficaz ? 'Eficaz' : 'Não eficaz',
            ];
        }
    }

    if (!function_exists('calcularEficaciaIntervencao')) {
        function calcularEficaciaIntervencao($aderiu, $aderencia, $temporalidade, $desempenho) {
            if (!$aderiu) {
                return null;
            }

            return round((100 + intval($aderencia) + intval($temporalidade) + intval($desempenho)) / 4);
        }
    }

    if (!function_exists('classesEficacia')) {
        function classesEficacia($valor) {
            if ($valor === null) {
                return ['text-dark', 'border-secondary'];
            }

            if ($valor >= 80) {
                return ['text-success', 'border-success'];
            }

            if ($valor >= 60) {
                return ['text-warning', 'border-warning'];
            }

            return ['text-danger', 'border-danger'];
        }
    }

    if (!function_exists('resumirCenariosBlade')) {
        function resumirCenariosBlade($labels) {
            if (!is_array($labels) || count($labels) === 0) {
                return '--';
            }

            $contagens = array_count_values($labels);
            $partes = [];
            foreach ($contagens as $label => $total) {
                $partes[] = $label . ': ' . $total;
            }

            return implode(' | ', $partes);
        }
    }
    @endphp

    <!-- Cards PRÉ e PÓS -->
    @php
        [$serverEficaciaTextClass, $serverEficaciaBorderClass] = classesEficacia($serverPosEficacia ?? null);
        $serverPreMedia = null;
        $serverPosMedia = null;
        $serverGanho = null;
        $serverAdesaoCount = null;
        $serverTotalInterv = null;
        $serverGanhoClass = null;
        $serverRelevanciaClass = null;
        $serverPreAderencia = null;
        $serverPreTemporalidade = null;
        $serverPreDesempenho = null;
        $serverPosAderencia = null;
        $serverPosTemporalidade = null;
        $serverPosDesempenho = null;
        $serverPosEficacia = null;

        if (isset($serverTurmaMetrics) && is_array($serverTurmaMetrics)) {
            $serverPreAderencia = (int) ($serverTurmaMetrics['pre']['aderencia'] ?? 0);
            $serverPreTemporalidade = (int) ($serverTurmaMetrics['pre']['temporalidade'] ?? 0);
            $serverPreDesempenho = (int) ($serverTurmaMetrics['pre']['desempenho'] ?? 0);
            $serverPosAderencia = (int) ($serverTurmaMetrics['pos']['aderencia'] ?? 0);
            $serverPosTemporalidade = (int) ($serverTurmaMetrics['pos']['temporalidade'] ?? 0);
            $serverPosDesempenho = (int) ($serverTurmaMetrics['pos']['desempenho'] ?? 0);
            $serverPreMedia = $serverPreDesempenho;
            $serverPosMedia = $serverPosDesempenho;
            $serverGanho = $serverPosMedia - $serverPreMedia;
            $serverGanhoClass = $serverGanho >= 0 ? 'bg-success' : 'bg-danger';
            $serverTotalInterv = (int) ($serverTurmaMetrics['total_pares_aderentes'] ?? 0);

            $adesaoParts = explode('/', (string) ($serverTurmaMetrics['pos']['adesao'] ?? '0/0'));
            $serverAdesaoCount = (int) ($adesaoParts[0] ?? 0);
            $serverAdesaoDenom = (int) ($adesaoParts[1] ?? 0);

            if ($serverGanho >= 15) {
                $serverRelevanciaClass = 'bg-success';
            } elseif ($serverGanho >= 0) {
                $serverRelevanciaClass = 'bg-warning text-dark';
            } else {
                $serverRelevanciaClass = 'bg-danger';
            }
        }
    @endphp
    <section id="resultadosLeitura" class="resultados-leitura mb-5" style="{{ isset($selectedTurma) && $selectedTurma ? '' : 'display: none;' }}">
    <div id="comparisonCards" class="resultados-leitura__resumo mb-4">
        <div class="analytics-comparison">
            <div class="analytics-compare-card analytics-compare-card--pre">
                <div class="analytics-compare-card__phase">Antes da intervenção</div>
                <div class="analytics-compare-card__metric">Desempenho médio</div>
                <div class="analytics-compare-card__value analytics-compare-card__value--pre" id="preDesempenho">{{ isset($serverPreMedia) ? $serverPreMedia . '%' : '--' }}</div>
            </div>

            <div id="posCard" class="analytics-compare-card analytics-compare-card--pos {{ isset($serverGanho) && $serverGanho < 0 ? 'is-negative' : 'is-positive' }}">
                <div class="analytics-compare-card__phase">Depois da intervenção</div>
                <div class="analytics-compare-card__metric">Desempenho médio</div>
                <div class="analytics-compare-card__value analytics-compare-card__value--pos" id="posDesempenho">{{ isset($serverPosMedia) ? $serverPosMedia . '%' : '--' }}</div>
                <div class="analytics-compare-card__badges">
                    <span id="ganhoPerda" class="badge badge-md {{ $serverGanhoClass }}">{{ isset($serverGanho) ? ($serverGanho >= 0 ? '↑ +' . abs($serverGanho) . '%' : '↓ ' . abs($serverGanho) . '%') : '--' }}</span>
                    <small id="relevanciaEstatistica" class="badge badge-sm {{ $serverRelevanciaClass }}">@if(isset($serverGanho))@if($serverGanho >= 15) ✓ Ganho relevante na turma @elseif($serverGanho >= 0) ⚠ Ganho modesto @else ✗ Perda de desempenho @endif @else -- @endif</small>
                </div>
            </div>
        </div>

        <x-eficacia-interpretacao :interpretacao="$interpretacaoTurma ?? null" :turma="$selectedTurma ?? null" />
    </div>
    </section>

    <div id="turmaProgressaoInline" class="mb-4" style="{{ isset($selectedTurma) && $selectedTurma ? '' : 'display: none;' }}">
        <div class="analytics-panel">
            <div class="analytics-panel__header">
                <div>
                    <h2 class="analytics-panel__title" id="turmaProgressaoTitle">Progressão — {{ $selectedTurma ?? '' }}</h2>
                    <p class="analytics-panel__subtitle">Quatro métricas comparadas entre PRÉ e PÓS-intervenção (médias da turma).</p>
                </div>
                <div class="chart-legend">
                    <span class="chart-legend__item"><span class="chart-legend__dot chart-legend__dot--pre"></span> PRÉ</span>
                    <span class="chart-legend__item"><span class="chart-legend__dot chart-legend__dot--pos"></span> PÓS</span>
                </div>
            </div>
            <div class="chart-canvas-wrap turma-progressao-chart-wrap">
                <canvas id="turmaProgressaoChart"></canvas>
            </div>
            <div class="mt-4">
                <h3 class="h6 fw-semibold mb-3">Análise por métrica</h3>
                <div id="turmaProgressaoAnalysis"></div>
            </div>
        </div>
    </div>

    <div id="resultadosTabsWrapper" class="analytics-detail" style="{{ isset($selectedTurma) && $selectedTurma ? '' : 'display: none;' }}">
    <div class="analytics-panel">
    <ul class="nav analytics-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="intervencoes-tab" data-bs-toggle="tab" data-bs-target="#intervencoes" type="button" role="tab">
                Por intervenção
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="alunos-tab" data-bs-toggle="tab" data-bs-target="#alunos" type="button" role="tab">
                Por aluno
            </button>
        </li>
    </ul>

    <div class="tab-content analytics-tab-content">
        <div class="tab-pane fade" id="alunos" role="tabpanel">
            <div class="analytics-section-header">
                <h3 class="analytics-section-header__title">Resultados por aluno</h3>
                <p class="analytics-section-header__hint">Comparativo pré e pós por intervenção de cada estudante.</p>
            </div>
            <div id="alunosContent">
                @if(isset($serverAlunos) && is_array($serverAlunos))
                    <div class="table-clean-wrap">
                        <table class="table table-hover table-clean table-clean-alunos align-middle">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Intervenção</th>
                                    <th>Cenário</th>
                                    <th>Comparativo</th>
                                    <th>Avaliação</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serverAlunos['alunos'] as $aluno)
                                    @foreach($aluno['intervencoes'] as $interv)
                                        @php
                                            $adesao = $interv['pos']['adesao'] ?? 'Não';
                                            $preDes = intval($interv['pre']['desempenho'] ?? 0);
                                            $posDes = intval($interv['pos']['desempenho'] ?? 0);
                                            $deltaDes = $posDes - $preDes;
                                                $cenarioRotulo = $interv['cenario'] ?? 'Não definido';
                                            $avaliacaoCenarioAluno = avaliarEficaciaCenario($interv['cenario'] ?? null, [
                                                'adesao_percentual' => ($adesao ?? '') === 'Sim' ? 100 : 0,
                                                'aderencia' => $interv['pos']['aderencia'] ?? 0,
                                                'temporalidade_inicio' => $interv['pos']['temporalidade_inicio'] ?? 0,
                                                'temporalidade_fim' => $interv['pos']['temporalidade_fim'] ?? 0,
                                                'desempenho' => $interv['pos']['desempenho'] ?? 0,
                                                'pre_desempenho' => $interv['pre']['desempenho'] ?? -1,
                                            ], [
                                                'aderencia' => (int) ($interv['limiar_aderencia'] ?? 25),
                                                'temporalidade_inicio' => (int) ($interv['limiar_temporalidade_inicio'] ?? 20),
                                                'temporalidade_fim' => (int) ($interv['limiar_temporalidade_fim'] ?? 60),
                                                'desempenho' => (int) ($interv['limiar_desempenho'] ?? 25),
                                            ]);
                                            $eficaciaAlunoClass = $avaliacaoCenarioAluno['eficaz'] === null ? 'is-warning' : ($avaliacaoCenarioAluno['eficaz'] ? 'is-success' : 'is-danger');
                                            $eficaciaAlunoLabel = $avaliacaoCenarioAluno['texto'];
                                            if ($deltaDes > 0) {
                                                $resultadoIcon = '↑';
                                            } elseif ($deltaDes < 0) {
                                                $resultadoIcon = '↓';
                                            } else {
                                                $resultadoIcon = '—';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="cell-title">{{ $aluno['aluno_nome'] }}</span>
                                                <span class="cell-subtitle">N. {{ $aluno['aluno_numero'] }}</span>
                                            </td>
                                            <td>
                                                <span class="cell-title">{{ $interv['titulo_exibicao'] ?? $interv['titulo'] }}</span>
                                            </td>
                                            <td>
                                                <span class="cell-title">{{ $cenarioRotulo }}</span>
                                            </td>
                                            <td>
                                                <div class="metric-stack compact">
                                                    <span class="metric-item"><span class="metric-label">Pré</span><span class="metric-value">{{ $preDes }}%</span></span>
                                                    <span class="metric-item"><span class="metric-label">Pós</span><span class="metric-value">{{ $posDes }}%</span></span>
                                                    <span class="metric-item"><span class="metric-label">Variação</span><span class="metric-value metric-value--trend {{ $deltaDes > 0 ? 'is-positive' : ($deltaDes < 0 ? 'is-negative' : 'is-neutral') }}">{{ $resultadoIcon }} {{ $deltaDes > 0 ? '+' : '' }}{{ $deltaDes }} pts</span></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="metric-stack compact">
                                                    <span class="metric-item"><span class="metric-label">Adesão</span><span class="metric-value">{{ $adesao }}</span></span>
                                                    <span class="metric-item metric-item-status"><span class="metric-label">Status</span><span class="metric-value"><span class="status-indicator {{ $eficaciaAlunoClass }}" title="{{ $eficaciaAlunoLabel }}" aria-label="{{ $eficaciaAlunoLabel }}"><span class="status-dot"></span></span></span></span>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-analytics view-aluno-progressao" 
                                                        data-aluno="{{ $aluno['aluno_nome'] }}"
                                                        data-aluno-id="{{ $aluno['aluno_numero'] }}"
                                                        data-intervencao="{{ $interv['titulo'] }}"
                                                        data-cenario="{{ $interv['cenario'] ?? '' }}"
                                                        data-pre-aderencia="{{ $interv['pre']['aderencia'] ?? 0 }}"
                                                        data-pre-temporalidade-inicio="{{ $interv['pre']['temporalidade_inicio'] ?? 0 }}"
                                                        data-pre-temporalidade-fim="{{ $interv['pre']['temporalidade_fim'] ?? 0 }}"
                                                        data-pre-desempenho="{{ $interv['pre']['desempenho'] ?? 0 }}"
                                                        data-pos-aderencia="{{ $interv['pos']['aderencia'] ?? 0 }}"
                                                        data-pos-temporalidade-inicio="{{ $interv['pos']['temporalidade_inicio'] ?? 0 }}"
                                                        data-pos-temporalidade-fim="{{ $interv['pos']['temporalidade_fim'] ?? 0 }}"
                                                        data-pos-desempenho="{{ $interv['pos']['desempenho'] ?? 0 }}"
                                                        data-adesao="{{ $adesao }}">
                                                    Detalhes
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">Selecione uma turma para ver os dados dos alunos</p>
                @endif
            </div>
        </div>

        <div class="tab-pane fade show active" id="intervencoes" role="tabpanel">
            <div class="analytics-section-header">
                <h3 class="analytics-section-header__title">Resultados por intervenção</h3>
                <p class="analytics-section-header__hint">Valores agregados da turma. Use «Detalhes» para entender cada indicador e receber sugestões pedagógicas.</p>
            </div>
            <div id="intervencoeContent">
                @if(isset($serverIntervencoes) && is_array($serverIntervencoes))
                    <div class="table-clean-wrap">
                        <table class="table table-hover table-clean table-clean-intervencoes align-middle">
                            <thead>
                                <tr>
                                    <th>Intervenção</th>
                                    <th>Cenário</th>
                                    <th>Indicadores</th>
                                    <th>Resultado</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serverIntervencoes['intervencoes'] as $interv)
                                    <tr>
                                        <td>
                                            <span class="cell-title">{{ $interv['titulo_exibicao'] ?? $interv['titulo'] }}</span>
                                        </td>
                                        <td>
                                            <span class="cell-title">{{ $interv['cenario'] ?? 'Não definido' }}</span>
                                        </td>
                                        @php 
                                            $adesaoStr = $interv['pos']['adesao'] ?? '0/0'; 
                                            $parts = explode('/', $adesaoStr); 
                                            $aTot = intval($parts[0] ?? 0); 
                                            $bTot = intval($parts[1] ?? 0); 
                                            $perc = $bTot > 0 ? round(($aTot / $bTot) * 100) : 0; 
                                            $eficaciaResumo = $interv['avaliacao_eficacia']['texto'] ?? '--';
                                            $eficaciaResumoClass = ($interv['avaliacao_eficacia']['eficaz'] ?? null) === null ? 'is-warning' : (!empty($interv['avaliacao_eficacia']['eficaz']) ? 'is-success' : 'is-danger');
                                        @endphp
                                        <td>
                                            <div class="metric-stack compact">
                                                <span class="metric-item"><span class="metric-label">Adesão</span><span class="metric-value">{{ $interv['pos']['adesao'] ?? '0/0' }} ({{ $perc }}%)</span></span>
                                                <span class="metric-item"><span class="metric-label">Aderência</span><span class="metric-value">{{ $interv['pos']['aderencia'] ?? 0 }}%</span></span>
                                                <span class="metric-item"><span class="metric-label">Desempenho</span><span class="metric-value">{{ $interv['pos']['desempenho'] ?? 0 }}%</span></span>
                                                <span class="metric-item"><span class="metric-label">Tempo</span><span class="metric-value">{{ $interv['pos']['temporalidade_inicio'] ?? 0 }} / {{ $interv['pos']['temporalidade_fim'] ?? 0 }} min</span></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="eficacia-label eficacia-label--{{ $eficaciaResumoClass === 'is-success' ? 'ok' : ($eficaciaResumoClass === 'is-danger' ? 'fail' : 'warn') }}">{{ $eficaciaResumo }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-analytics ver-interpretacao"
                                                    data-intervencao-id="{{ $interv['id'] }}"
                                                    data-turma="{{ $selectedTurma ?? '' }}"
                                                    data-titulo="{{ $interv['titulo_exibicao'] ?? $interv['titulo'] }}"
                                                    title="Análise pedagógica com explicação de cada métrica">
                                                Detalhes
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">Selecione uma turma para ver os dados das intervenções</p>
                @endif
            </div>
        </div>
    </div>
    </div>
    </div>

    <div class="modal fade" id="progressaoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content analytics-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Progressão: PRÉ vs PÓS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="chart-legend mb-3">
                        <span class="chart-legend__item"><span class="chart-legend__dot chart-legend__dot--pre"></span> PRÉ-intervenção</span>
                        <span class="chart-legend__item"><span class="chart-legend__dot chart-legend__dot--pos"></span> PÓS-intervenção</span>
                    </div>
                    <div id="progressaoChartContainer" class="chart-canvas-wrap progressao-chart-container">
                        <canvas id="progressaoChart"></canvas>
                    </div>
                    <h6 class="h6 fw-semibold mt-4 mb-3">Análise por métrica</h6>
                    <div id="progressaoAnalysis"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="interpretacaoIntervencaoModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content analytics-modal">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="interpretacaoTitle">Análise da intervenção</h5>
                        <p class="modal-subtitle mb-0 small text-secondary" id="interpretacaoSubtitle"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="interpretacaoContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Detalhes da Turma -->
    <!-- Modal para Detalhes da Turma (removido — agora reusa o modal de progressão) -->
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
    $chartsThemeJsV = @filemtime(public_path('js/charts-theme.js')) ?: time();
    $interpretacaoUiJsV = @filemtime(public_path('js/interpretacao-ui.js')) ?: time();
    $insightsJsV = @filemtime(public_path('js/resultados-insights.js')) ?: time();
@endphp
<script>window.INTERPRETACAO_AJUDA = @json(config('interpretacao_ajuda'));</script>
<script src="{{ asset('js/charts-theme.js') }}?v={{ $chartsThemeJsV }}"></script>
<script src="{{ asset('js/interpretacao-ui.js') }}?v={{ $interpretacaoUiJsV }}"></script>
<script src="{{ asset('js/resultados-insights.js') }}?v={{ $insightsJsV }}"></script>


<script>
let progressaoChartInstance = null;
let progressaoModalInstance = null;

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function temInterpretacaoCompleta(interp) {
    return Boolean(
        interp && (
            interp.classificacao_rotulo
            || interp.sintese
            || interp.pre_desempenho !== undefined
        )
    );
}

function renderInterpretacaoTurma(interp, turmaNome) {
    const panel = document.getElementById('eficaciaInterpretacaoPanel');
    const content = document.getElementById('eficaciaInterpretacaoContent');
    const leitura = document.getElementById('resultadosLeitura');
    if (!panel || !content) return;

    const turma = turmaNome || interp?.turma || turmaAtual || '';
    if (!turma) {
        panel.style.display = 'none';
        content.innerHTML = '';
        return;
    }

    if (leitura) leitura.style.display = '';
    panel.style.display = '';

    if (!temInterpretacaoCompleta(interp)) {
        const msg = interp?.mensagem || 'Não há dados suficientes para interpretar os resultados desta turma.';
        content.innerHTML = `<p class="text-muted mb-0">${escapeHtml(msg)}</p>`;
        return;
    }

    const renderFn = window.ResultadosInsights?.renderInterpretacaoTurmaContent
        || window.InterpretacaoUI?.buildInterpretacaoTurmaHtml;
    const html = typeof renderFn === 'function' ? renderFn(interp) : '';
    content.innerHTML = html || '<p class="text-muted mb-0">Não foi possível exibir a interpretação.</p>';
    if (window.InterpretacaoUI?.initPopovers) {
        window.InterpretacaoUI.initPopovers(panel);
    }
}

function mostrarInterpretacaoCarregando() {
    const content = document.getElementById('eficaciaInterpretacaoContent');
    if (content) {
        content.innerHTML = '<p class="text-muted mb-0 interpretacao-carregando">Carregando interpretação da turma…</p>';
    }
}

// Função para mostrar progressão em modal (fora do DOMContentLoaded)
function mostrarProgressao(intervencao) {
    // Criar modal apenas uma vez
    if (!progressaoModalInstance) {
        progressaoModalInstance = new bootstrap.Modal(document.getElementById('progressaoModal'), {
            backdrop: 'static',
            keyboard: false
        });
    }
    
    const cenarioTexto = intervencao.cenario ? ` | Cenário: ${rotuloCenario(intervencao.cenario)}` : '';
    document.getElementById('progressaoModal').querySelector('.modal-title').textContent = 
        `Progressão: ${intervencao.titulo}${cenarioTexto}`;
    
    if (!intervencao.aderido) {
        // Esconder gráfico
        document.getElementById('progressaoChartContainer').style.display = 'none';
        
        const aviso = `
            <div class="alert alert-warning mb-4">
                <strong>⚠️ Sem Participação</strong><br>
                O aluno não participou desta intervenção (Adesão: Não). Métricas não disponíveis.
            </div>
        `;
        document.getElementById('progressaoAnalysis').innerHTML = aviso;
        progressaoModalInstance.show();
        return;
    }
    
    // Mostrar gráfico
    document.getElementById('progressaoChartContainer').style.display = 'block';
    
    setTimeout(() => {
        const metricas = ['Aderência', 'Temporalidade Início', 'Temporalidade Finalização', 'Desempenho'];
        const antes = [
            intervencao.antes.aderencia,
            intervencao.antes.temporalidadeInicio,
            intervencao.antes.temporalidadeFim,
            intervencao.antes.desempenho
        ];
        const depois = [
            intervencao.depois.aderencia,
            intervencao.depois.temporalidadeInicio,
            intervencao.depois.temporalidadeFim,
            intervencao.depois.desempenho
        ];

        const ctx = document.getElementById('progressaoChart').getContext('2d');
        
        if (progressaoChartInstance) {
            progressaoChartInstance.destroy();
        }

        // Garantir que os dados são números
        const beforeData = antes.map(v => parseFloat(v));
        const afterData = depois.map(v => parseFloat(v));

        const chartTheme = window.IntervencoesCharts || {};
        const dsPre = chartTheme.datasetPre || { label: 'PRÉ-intervenção', backgroundColor: '#94a3b8' };
        const dsPos = chartTheme.datasetPos || { label: 'PÓS-intervenção', backgroundColor: '#0f766e' };

        progressaoChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: metricas,
                datasets: [
                    { ...dsPre, data: beforeData, maxBarThickness: 32 },
                    { ...dsPos, data: afterData, maxBarThickness: 32 },
                ]
            },
            options: {
                ...(chartTheme.baseOptions ? chartTheme.baseOptions() : { responsive: true, maintainAspectRatio: false }),
                scales: {
                    y: chartTheme.scaleOptions ? chartTheme.scaleOptions() : { beginAtZero: true },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartTheme.colors?.text || '#64748b' },
                    },
                },
                plugins: {
                    legend: { display: true, position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const metrica = context.label || '';
                                const sufixo = metrica.includes('Temporalidade') ? ' min' : '%';
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + sufixo;
                            }
                        }
                    }
                }
            }
        });

        const metricas_names = ['Aderência', 'Temporalidade Início', 'Temporalidade Finalização', 'Desempenho'];
        document.getElementById('progressaoAnalysis').innerHTML = renderMetricAnalysisGrid(metricas_names, antes, depois);
        progressaoModalInstance.show();
    }, 300);
}


let interpretacaoIntervencaoModalInstance = null;

function mostrarInterpretacaoIntervencao(turma, intervencaoId, tituloFallback) {
    const modalEl = document.getElementById('interpretacaoIntervencaoModal');
    const content = document.getElementById('interpretacaoContent');
    const titleEl = document.getElementById('interpretacaoTitle');
    const subtitleEl = document.getElementById('interpretacaoSubtitle');
    if (!modalEl || !content || !turma || intervencaoId == null || intervencaoId === '') return;

    if (!interpretacaoIntervencaoModalInstance) {
        interpretacaoIntervencaoModalInstance = new bootstrap.Modal(modalEl);
    }

    titleEl.textContent = tituloFallback ? `Análise: ${tituloFallback}` : 'Análise da intervenção';
    if (subtitleEl) subtitleEl.textContent = `Turma ${turma}`;
    content.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Preparando análise pedagógica…</div>';
    interpretacaoIntervencaoModalInstance.show();

    fetch(`/api/turma/${encodeURIComponent(turma)}/intervencao/${intervencaoId}/analise`)
        .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
        .then(data => {
            titleEl.textContent = `Análise: ${data.titulo || tituloFallback || 'Intervenção'}`;
            if (subtitleEl) {
                subtitleEl.textContent = `Turma ${turma} · Cenário ${data.cenario || '—'}`;
            }
            const renderFn = window.ResultadosInsights?.renderIntervencaoAnalise;
            content.innerHTML = typeof renderFn === 'function'
                ? renderFn(data)
                : '<p class="text-muted">Não foi possível exibir a análise.</p>';
        })
        .catch(err => {
            console.error('Análise intervenção:', err);
            content.innerHTML = '<p class="text-danger mb-0">Erro ao carregar a análise. Tente novamente.</p>';
        });
}

// Função para interpretar valores de métricas
function interpretarMetrica(tipo, valor) {
    if (tipo === 'aderencia') {
        if (valor >= 80) return '✓ Excelente - Tarefas completadas com precisão';
        if (valor >= 60) return '◐ Bom - Maioria das tarefas completadas';
        if (valor >= 40) return '⚠ Razoável - Algumas dificuldades na execução';
        return '✗ Insuficiente - Sérias dificuldades na execução';
    }
    if (tipo === 'temporalidade') {
        if (valor <= 15) return '✓ Excelente - Início/finalização muito ágeis';
        if (valor <= 30) return '◐ Bom - Tempo adequado de início/finalização';
        if (valor <= 60) return '⚠ Razoável - Tempo acima do esperado';
        return '✗ Insuficiente - Tempo muito elevado para concluir';
    }
    if (tipo === 'desempenho') {
        if (valor >= 80) return '✓ Excelente - Realização muito acima da média';
        if (valor >= 60) return '◐ Bom - Realização acima da média';
        if (valor >= 40) return '⚠ Razoável - Realização dentro da média';
        return '✗ Abaixo da média - Necessita melhorias';
    }
    return '';
}

function classificarTemporalidade(valor) {
    const numero = Number(valor || 0);
    if (numero <= 15) return 'Muito rápido';
    if (numero <= 30) return 'Adequado';
    if (numero <= 60) return 'Lento';
    return 'Muito lento';
}

function getClasseComparativoMetrica(tipo, valorAtual, valorEsperado) {
    const atual = Number(valorAtual || 0);
    const esperado = Number(valorEsperado || 0);
    const ehTemporalidade = String(tipo).includes('temporalidade');

    if (atual === esperado) return 'text-secondary';

    if (ehTemporalidade) {
        return atual < esperado ? 'text-success' : 'text-danger';
    }

    return atual > esperado ? 'text-success' : 'text-danger';
}

function rotuloCenario(valor) {
    const normalizado = String(valor || '').trim().toLowerCase();
    const semAcento = normalizado.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    if (['leve', 'flexivel'].includes(semAcento)) return 'Flexível';
    if (['rigido', 'moderado', 'modelado', 'modelo'].includes(semAcento)) return 'Moderado';
    if (['dificil', 'personalizado'].includes(semAcento)) return 'Difícil';
    return 'Não definido';
}

function tituloIntervencaoTabela(cenario, titulo) {
    const normalizado = String(cenario || '').trim().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    if (['leve', 'flexivel'].includes(normalizado)) return 'Cenário 1';
    if (['dificil', 'personalizado'].includes(normalizado)) return 'Cenário 2';

    return titulo || 'Intervenção';
}

function perfilCenario(valor) {
    const normalizado = String(valor || '').trim().toLowerCase();
    const semAcento = normalizado.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    if (['leve', 'flexivel'].includes(semAcento)) {
        return { adesao: 'sim', aderencia: 25, temporalidade_inicio: 20, temporalidade_fim: 60, desempenho: 25 };
    }

    if (['rigido', 'moderado', 'modelado', 'modelo'].includes(semAcento)) {
        return { adesao: 'sim', aderencia: 60, temporalidade_inicio: 15, temporalidade_fim: 45, desempenho: 60 };
    }

    if (['dificil', 'personalizado'].includes(semAcento)) {
        return { adesao: 'sim', aderencia: 80, temporalidade_inicio: 10, temporalidade_fim: 30, desempenho: 80 };
    }

    return { adesao: 'sim', aderencia: 0, temporalidade_inicio: 0, temporalidade_fim: 0, desempenho: 0 };
}

function avaliarCenario(valor, metricas) {
    const cenario = rotuloCenario(valor);
    const perfil = perfilCenario(valor);

    const adesaoPercentual = Number(metricas?.adesao_percentual || 0);
    const aderencia = Number(metricas?.aderencia || 0);
    const temporalidadeInicio = Number(metricas?.temporalidade_inicio || 0);
    const temporalidadeFim = Number(metricas?.temporalidade_fim || 0);
    const desempenho = Number(metricas?.desempenho || 0);
    const preDesempenho = Number(metricas?.pre_desempenho ?? -1);

    if (adesaoPercentual <= 0) {
        return {
            cenario,
            cenarioResultado: 'Sem participação',
            perfil,
            eficaz: null,
            temResultado: false,
            texto: 'Sem participação'
        };
    }

    if (preDesempenho >= 0 && desempenho <= preDesempenho) {
        return {
            cenario,
            cenarioResultado: 'Sem ganho em desempenho',
            perfil,
            eficaz: false,
            temResultado: true,
            texto: 'Não eficaz'
        };
    }

    let cenarioResultado = 'Abaixo dos critérios';
    if (aderencia >= 80 && temporalidadeInicio <= 10 && temporalidadeFim <= 30 && desempenho >= 80) {
        cenarioResultado = 'Difícil';
    } else if (aderencia >= 60 && temporalidadeInicio <= 15 && temporalidadeFim <= 45 && desempenho >= 60) {
        cenarioResultado = 'Moderado';
    } else if (aderencia >= 25 && temporalidadeInicio <= 20 && temporalidadeFim <= 60 && desempenho >= 25) {
        cenarioResultado = 'Flexível';
    }

    const eficaz = cenario === 'Flexível'
        ? ['Flexível', 'Moderado', 'Difícil'].includes(cenarioResultado)
        : cenario === 'Moderado'
            ? ['Moderado', 'Difícil'].includes(cenarioResultado)
            : cenarioResultado === 'Difícil';

    return { cenario, cenarioResultado, perfil, eficaz, temResultado: true, texto: eficaz ? 'Eficaz' : 'Sem eficácia' };
}

function classificarResultadoVsConfigurado(cenarioConfigurado, cenarioResultado) {
    const ordem = {
        'Flexível': 1,
        'Moderado': 2,
        'Difícil': 3
    };

    const nivelConfigurado = ordem[String(cenarioConfigurado || '')] || 0;
    const nivelResultado = ordem[String(cenarioResultado || '')] || 0;

    if (String(cenarioResultado || '') === 'Sem participação') {
        return {
            classe: 'is-warning',
            titulo: 'Sem participação dos alunos'
        };
    }

    if (nivelResultado > nivelConfigurado) {
        return {
            classe: 'is-success',
            titulo: 'Ganho em relação ao cenário configurado'
        };
    }

    if (nivelResultado < nivelConfigurado) {
        return {
            classe: 'is-danger',
            titulo: 'Perda em relação ao cenário configurado'
        };
    }

    return {
        classe: 'is-success',
        titulo: 'Igual ao cenário configurado'
    };
}

function resumirCenarios(labels) {
    if (!labels.length) return '--';
    const counts = labels.reduce((acc, label) => {
        acc[label] = (acc[label] || 0) + 1;
        return acc;
    }, {});

    return Object.entries(counts).map(([label, total]) => `${label}: ${total}`).join(' | ');
}

function formatMetricaValor(nomeMetrica, valor) {
    const numero = Number(valor || 0);
    return nomeMetrica.includes('Temporalidade') ? `${numero} min` : `${numero}%`;
}

function formatMetricaDelta(nomeMetrica, delta) {
    const numero = Number(delta || 0);
    const sinal = numero >= 0 ? '+' : '';
    return nomeMetrica.includes('Temporalidade') ? `${sinal}${numero} min` : `${sinal}${numero}%`;
}

function calcularGanhoMetrica(nomeMetrica, valorPre, valorPos) {
    const pre = Number(valorPre || 0);
    const pos = Number(valorPos || 0);

    // Para temporalidade, quanto menor o tempo no POS, maior o ganho.
    if (nomeMetrica.includes('Temporalidade')) {
        return pre - pos;
    }

    return pos - pre;
}

function renderMetricAnalysisGrid(nomes, antes, depois) {
    let html = '<div class="metric-analysis-grid">';
    for (let i = 0; i < nomes.length; i++) {
        const nome = nomes[i];
        const ganho = calcularGanhoMetrica(nome, antes[i], depois[i]);
        const ganhoClass = ganho >= 0 ? 'bg-success' : 'bg-danger';
        html += `
            <div class="metric-analysis-card">
                <div class="metric-analysis-card__title">${nome}</div>
                <div class="metric-analysis-card__compare">
                    <div>
                        <span class="metric-analysis-card__phase">Pré</span>
                        <div class="metric-analysis-card__value--pre">${formatMetricaValor(nome, antes[i])}</div>
                    </div>
                    <div>
                        <span class="metric-analysis-card__phase">Pós</span>
                        <div class="metric-analysis-card__value--pos">${formatMetricaValor(nome, depois[i])}</div>
                    </div>
                </div>
                <div class="metric-analysis-card__delta">
                    <span class="metric-analysis-card__delta-label">Variação</span>
                    <span class="badge ${ganhoClass}">${formatMetricaDelta(nome, ganho)}</span>
                </div>
            </div>
        `;
    }
    return html + '</div>';
}

function calcularEficaciaIntervencao(aderiu, aderencia, temporalidade, desempenho) {
    if (!aderiu) {
        return null;
    }

    return Math.round((100 + Number(aderencia || 0) + Number(temporalidade || 0) + Number(desempenho || 0)) / 4);
}

function getEficaciaClasses(valor) {
    if (valor === null || Number.isNaN(Number(valor))) {
        return {
            textClass: 'text-dark',
            borderClass: 'border-secondary'
        };
    }

    const numero = Number(valor);
    if (numero >= 80) {
        return {
            textClass: 'text-success',
            borderClass: 'border-success'
        };
    }

    if (numero >= 60) {
        return {
            textClass: 'text-warning',
            borderClass: 'border-warning'
        };
    }

    return {
        textClass: 'text-danger',
        borderClass: 'border-danger'
    };
}

// Função simples de debug que escreve no painel `#debugLog`
function logDebug(msg) {
    try {
        const el = document.getElementById('debugLog');
        if (!el) return;
        el.style.display = '';
        const time = new Date().toLocaleTimeString();
        el.textContent = `[${time}] ${msg}\n` + el.textContent;
    } catch (e) {
        // fallback silencioso
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let progressaoChartInstance = null;
    let turmaProgressaoChartInstance = null;
    let turmaAtual = null;
    let dadosTurmaAtual = null;
    let intervencaoSelecionada = 'all';

    const intervencaoFiltro = document.getElementById('intervencaoFiltro');
    const intervencaoFiltroWrapper = document.getElementById('intervencaoFiltroWrapper');

    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    function normalizeData(data) {
        return {
            turma: data?.turma || '',
            alunos: Array.isArray(data?.alunos) ? data.alunos : []
        };
    }

    function getIntervencoesFromData(data) {
        const map = new Map();
        (data.alunos || []).forEach(aluno => {
            (aluno.intervencoes || []).forEach(interv => {
                if (!map.has(String(interv.intervencao_id))) {
                    map.set(String(interv.intervencao_id), {
                        id: interv.intervencao_id,
                        titulo: tituloIntervencaoTabela(interv.cenario, interv.titulo),
                    });
                }
            });
        });

        return Array.from(map.values()).sort((a, b) => (a.titulo || '').localeCompare(b.titulo || ''));
    }

    function populateIntervencaoFiltro(data) {
        if (!intervencaoFiltro) return;

        const options = getIntervencoesFromData(data);
        const previousValue = intervencaoSelecionada || 'all';

        intervencaoFiltro.innerHTML = '<option value="all">Todas as intervenções</option>';
        options.forEach(item => {
            const opt = document.createElement('option');
            opt.value = String(item.id);
            opt.textContent = item.titulo;
            intervencaoFiltro.appendChild(opt);
        });

        const exists = Array.from(intervencaoFiltro.options).some(o => o.value === previousValue);
        intervencaoSelecionada = exists ? previousValue : 'all';
        intervencaoFiltro.value = intervencaoSelecionada;
    }

    function filterDataByIntervencao(data, intervencaoId) {
        const base = normalizeData(data);
        if (!intervencaoId || intervencaoId === 'all') return base;

        return {
            turma: base.turma,
            alunos: base.alunos.map(aluno => ({
                ...aluno,
                intervencoes: (aluno.intervencoes || []).filter(interv => String(interv.intervencao_id) === String(intervencaoId))
            }))
        };
    }

    function computeTurmaProgressMetrics(alunosData) {
        let preAderencia = 0;
        let preTemporalidadeInicio = 0;
        let preTemporalidadeFim = 0;
        let preDesempenho = 0;
        let posAderencia = 0;
        let posTemporalidadeInicio = 0;
        let posTemporalidadeFim = 0;
        let posDesempenho = 0;
        let posEficacia = 0;
        let total = 0;

        (alunosData.alunos || []).forEach(aluno => {
            (aluno.intervencoes || []).forEach(interv => {
                const aderiu = (interv.pos?.adesao || 'Não') === 'Sim';
                if (!aderiu) return;

                preAderencia += Number(interv.pre?.aderencia || 0);
                preTemporalidadeInicio += Number(interv.pre?.temporalidade_inicio || 0);
                preTemporalidadeFim += Number(interv.pre?.temporalidade_fim || 0);
                preDesempenho += Number(interv.pre?.desempenho || 0);
                posAderencia += Number(interv.pos?.aderencia || 0);
                posTemporalidadeInicio += Number(interv.pos?.temporalidade_inicio || 0);
                posTemporalidadeFim += Number(interv.pos?.temporalidade_fim || 0);
                posDesempenho += Number(interv.pos?.desempenho || 0);
                posEficacia += calcularEficaciaIntervencao(
                    aderiu,
                    interv.pos?.aderencia || 0,
                    Math.round((Number(interv.pos?.temporalidade_inicio || 0) + Number(interv.pos?.temporalidade_fim || 0)) / 2),
                    interv.pos?.desempenho || 0
                ) || 0;
                total++;
            });
        });

        return {
            pre: {
                aderencia: total > 0 ? Math.round(preAderencia / total) : 0,
                temporalidade_inicio: total > 0 ? Math.round(preTemporalidadeInicio / total) : 0,
                temporalidade_fim: total > 0 ? Math.round(preTemporalidadeFim / total) : 0,
                desempenho: total > 0 ? Math.round(preDesempenho / total) : 0,
            },
            pos: {
                aderencia: total > 0 ? Math.round(posAderencia / total) : 0,
                temporalidade_inicio: total > 0 ? Math.round(posTemporalidadeInicio / total) : 0,
                temporalidade_fim: total > 0 ? Math.round(posTemporalidadeFim / total) : 0,
                desempenho: total > 0 ? Math.round(posDesempenho / total) : 0,
            },
            eficacia: total > 0 ? Math.round(posEficacia / total) : null,
        };
    }

    function renderTurmaProgressaoInline(turma, metrics) {
        const wrap = document.getElementById('turmaProgressaoInline');
        const title = document.getElementById('turmaProgressaoTitle');
        const analysis = document.getElementById('turmaProgressaoAnalysis');
        const canvas = document.getElementById('turmaProgressaoChart');

        if (!wrap || !title || !analysis || !canvas || !turma || !metrics) return;

        wrap.style.display = '';
        title.textContent = `Progressão — ${turma}`;

        const antes = [
            Number(metrics.pre?.aderencia || 0),
            Number(metrics.pre?.temporalidade_inicio || 0),
            Number(metrics.pre?.temporalidade_fim || 0),
            Number(metrics.pre?.desempenho || 0)
        ];
        const depois = [
            Number(metrics.pos?.aderencia || 0),
            Number(metrics.pos?.temporalidade_inicio || 0),
            Number(metrics.pos?.temporalidade_fim || 0),
            Number(metrics.pos?.desempenho || 0)
        ];

        if (turmaProgressaoChartInstance) {
            turmaProgressaoChartInstance.destroy();
        }

        const chartTheme = window.IntervencoesCharts || {};
        const dsPre = chartTheme.datasetPre || { label: 'PRÉ-intervenção', backgroundColor: '#94a3b8' };
        const dsPos = chartTheme.datasetPos || { label: 'PÓS-intervenção', backgroundColor: '#0f766e' };

        turmaProgressaoChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Aderência', 'Temporalidade Início', 'Temporalidade Finalização', 'Desempenho'],
                datasets: [
                    { ...dsPre, data: antes, maxBarThickness: 32 },
                    { ...dsPos, data: depois, maxBarThickness: 32 },
                ]
            },
            options: {
                ...(chartTheme.baseOptions ? chartTheme.baseOptions() : { responsive: true, maintainAspectRatio: false }),
                scales: {
                    y: chartTheme.scaleOptions ? chartTheme.scaleOptions() : { beginAtZero: true },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartTheme.colors?.text || '#64748b' },
                    },
                },
                plugins: {
                    legend: { display: true, position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const metrica = context.label || '';
                                const sufixo = metrica.includes('Temporalidade') ? ' min' : '%';
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + sufixo;
                            }
                        }
                    }
                }
            }
        });

        const nomes = ['Aderência', 'Temporalidade Início', 'Temporalidade Finalização', 'Desempenho'];
        analysis.innerHTML = renderMetricAnalysisGrid(nomes, antes, depois);
    }

    function aplicarCardsDesempenhoTurma(stats) {
        const mediaPreDesempenho = Number(stats?.pre?.desempenho ?? 0);
        const mediaPosDesempenho = Number(stats?.pos?.desempenho ?? 0);
        const ganho = mediaPosDesempenho - mediaPreDesempenho;
        const ganhoClass = ganho >= 0 ? 'bg-success' : 'bg-danger';
        const ganhoText = ganho >= 0 ? '↑ +' : '↓ ';

        let relevanciaClass = 'bg-secondary';
        let relevanciaText = 'Sem dados';
        if ((stats?.total_pares_aderentes ?? 0) > 0) {
            if (ganho >= 15) {
                relevanciaClass = 'bg-success';
                relevanciaText = '✓ Ganho relevante na turma';
            } else if (ganho >= 0) {
                relevanciaClass = 'bg-warning text-dark';
                relevanciaText = '⚠ Ganho modesto';
            } else {
                relevanciaClass = 'bg-danger';
                relevanciaText = '✗ Perda de desempenho';
            }
        }

        document.getElementById('preDesempenho').textContent = mediaPreDesempenho + '%';
        document.getElementById('posDesempenho').textContent = mediaPosDesempenho + '%';
        document.getElementById('posDesempenho').className = 'analytics-compare-card__value analytics-compare-card__value--pos';
        document.getElementById('posCard').className = `analytics-compare-card analytics-compare-card--pos ${ganho < 0 ? 'is-negative' : 'is-positive'}`;
        document.getElementById('ganhoPerda').className = `badge ${ganhoClass}`;
        document.getElementById('ganhoPerda').textContent = `${ganhoText}${Math.abs(ganho)}%`;
        document.getElementById('relevanciaEstatistica').className = `badge ${relevanciaClass}`;
        document.getElementById('relevanciaEstatistica').textContent = relevanciaText;
    }

    function renderTabelasTurma(data, turmaNome) {
        const filteredData = filterDataByIntervencao(data, intervencaoSelecionada);
        logDebug('Chamando carregarAlunosPorTurma (filtrado)');
        carregarAlunosPorTurma(filteredData);
        logDebug('Chamando carregarIntervencoesPorTurma (filtrado)');
        carregarIntervencoesPorTurma(filteredData, turmaNome);
    }

    function renderResultadosFiltrados(data) {
        const filteredData = filterDataByIntervencao(data, intervencaoSelecionada);
        const turmaNome = turmaAtual || data.turma || '';
        const params = new URLSearchParams();
        if (intervencaoSelecionada && intervencaoSelecionada !== 'all') {
            params.set('intervencao', String(intervencaoSelecionada));
        }
        const qs = params.toString();

        mostrarInterpretacaoCarregando();

        fetch(`/api/turma/${encodeURIComponent(turmaNome)}${qs ? `?${qs}` : ''}`)
            .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
            .then(payload => {
                const interpretacao = payload.interpretacao || null;
                aplicarCardsDesempenhoTurma(payload);
                renderTurmaProgressaoInline(turmaNome, payload);
                renderInterpretacaoTurma(interpretacao, turmaNome);
                renderTabelasTurma(data, turmaNome);
            })
            .catch(err => {
                console.error('Métricas da turma:', err);
                const fallback = computeTurmaProgressMetrics(filteredData);
                aplicarCardsDesempenhoTurma({ ...fallback, total_pares_aderentes: 1 });
                renderTurmaProgressaoInline(turmaNome, fallback);
                renderInterpretacaoTurma({
                    turma: turmaNome,
                    mensagem: 'Não foi possível atualizar os resultados. Os dados das tabelas abaixo foram mantidos.',
                }, turmaNome);
                renderTabelasTurma(data, turmaNome);
            });
    }

    // Filtro geral de turma
    document.getElementById('turmaFiltro').addEventListener('change', function() {
        const turma = this.value;
        const turmaEncoded = encodeURIComponent(turma);
        const turmaFiltroForm = document.getElementById('turmaFiltroForm');
        const resultadosTabsWrapper = document.getElementById('resultadosTabsWrapper');
        const resultadosEmptyState = document.getElementById('resultadosEmptyState');
        turmaAtual = turma;
        
        logDebug('Turma selecionada: ' + turma);
        
        if (!turma) {
            const leitura = document.getElementById('resultadosLeitura');
            if (leitura) leitura.style.display = 'none';
            document.getElementById('turmaProgressaoInline').style.display = 'none';
            renderInterpretacaoTurma(null, '');
            if (intervencaoFiltroWrapper) intervencaoFiltroWrapper.style.display = 'none';
            if (intervencaoFiltro) {
                intervencaoFiltro.innerHTML = '<option value="all">Todas as intervenções</option>';
                intervencaoFiltro.value = 'all';
            }
            dadosTurmaAtual = null;
            intervencaoSelecionada = 'all';
            if (turmaProgressaoChartInstance) {
                turmaProgressaoChartInstance.destroy();
                turmaProgressaoChartInstance = null;
            }
            if (resultadosTabsWrapper) resultadosTabsWrapper.style.display = 'none';
            if (resultadosEmptyState) resultadosEmptyState.style.display = '';
            document.getElementById('alunosContent').innerHTML = '<p class="text-muted text-center py-4">Selecione uma turma para ver os dados dos alunos</p>';
            document.getElementById('intervencoeContent').innerHTML = '<p class="text-muted text-center py-4">Selecione uma turma para ver os dados das intervenções</p>';
            return;
        }

        const leitura = document.getElementById('resultadosLeitura');
        if (leitura) leitura.style.display = '';
        const interpPanel = document.getElementById('eficaciaInterpretacaoPanel');
        const interpContent = document.getElementById('eficaciaInterpretacaoContent');
        if (interpPanel) interpPanel.style.display = '';
        if (interpContent) {
            mostrarInterpretacaoCarregando();
        }
        if (intervencaoFiltroWrapper) intervencaoFiltroWrapper.style.display = '';
        if (resultadosTabsWrapper) resultadosTabsWrapper.style.display = '';
        if (resultadosEmptyState) resultadosEmptyState.style.display = 'none';

        // Buscar dados da turma
        fetch(`/api/turma/${turmaEncoded}/alunos`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                logDebug('Dados recebidos: ' + (data && data.turma ? ('turma=' + data.turma) : JSON.stringify(data).slice(0,200)));
                dadosTurmaAtual = normalizeData(data);
                intervencaoSelecionada = 'all';
                populateIntervencaoFiltro(dadosTurmaAtual);
                renderResultadosFiltrados(dadosTurmaAtual);
            })
            .catch(error => {
                console.error('Erro ao buscar dados:', error);
                logDebug('Falha no filtro via API. Aplicando fallback com submit do formulário.');
                if (turmaFiltroForm) {
                    turmaFiltroForm.submit();
                }
            });
    });

    // Carregar dados de alunos
    function carregarAlunosPorTurma(data) {
        logDebug('Carregando alunos: ' + (data && data.alunos ? ('alunos=' + data.alunos.length) : JSON.stringify(data).slice(0,200)));
        let html = `<div class="table-clean-wrap">
            <table class="table table-hover table-clean table-clean-alunos align-middle">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Intervenção</th>
                        <th>Cenário</th>
                        <th>Comparativo</th>
                        <th>Avaliação</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>`;
        
        (data.alunos || []).forEach(aluno => {
            (aluno.intervencoes || []).forEach(interv => {
                if (!interv?.pos) return;
                const preDes = Number(interv.pre?.desempenho || 0);
                const posDes = Number(interv.pos?.desempenho || 0);
                const delta = posDes - preDes;
                const resultadoIcon = delta > 0 ? '↑' : (delta < 0 ? '↓' : '—');
                const cenarioLabel = rotuloCenario(interv.cenario);
                const avaliacaoCenarioAluno = avaliarCenario(interv.cenario, {
                    adesao_percentual: interv.pos.adesao === 'Sim' ? 100 : 0,
                    aderencia: interv.pos?.aderencia || 0,
                    temporalidade_inicio: interv.pos?.temporalidade_inicio || 0,
                    temporalidade_fim: interv.pos?.temporalidade_fim || 0,
                    desempenho: interv.pos?.desempenho || 0
                });
                const eficaciaClass = avaliacaoCenarioAluno.eficaz === null ? 'is-warning' : (avaliacaoCenarioAluno.eficaz ? 'is-success' : 'is-danger');
                const eficaciaLabel = avaliacaoCenarioAluno.texto;
                html += `
                    <tr>
                        <td>
                            <span class="cell-title">${aluno.aluno_nome}</span>
                            <span class="cell-subtitle">N. ${aluno.aluno_numero}</span>
                        </td>
                        <td>
                            <span class="cell-title">${tituloIntervencaoTabela(interv.cenario, interv.titulo)}</span>
                        </td>
                        <td>
                            <span class="cell-title">${cenarioLabel}</span>
                        </td>
                        <td>
                            <div class="metric-stack compact">
                                <span class="metric-item"><span class="metric-label">Pré</span><span class="metric-value">${preDes}%</span></span>
                                <span class="metric-item"><span class="metric-label">Pós</span><span class="metric-value">${posDes}%</span></span>
                                <span class="metric-item"><span class="metric-label">Variação</span><span class="metric-value metric-value--trend ${delta > 0 ? 'is-positive' : (delta < 0 ? 'is-negative' : 'is-neutral')}">${resultadoIcon} ${delta > 0 ? '+' : ''}${delta} pts</span></span>
                            </div>
                        </td>
                        <td>
                            <div class="metric-stack compact">
                                <span class="metric-item"><span class="metric-label">Adesão</span><span class="metric-value">${interv.pos.adesao}</span></span>
                                <span class="metric-item metric-item-status"><span class="metric-label">Status</span><span class="metric-value"><span class="status-indicator ${eficaciaClass}" title="${eficaciaLabel}" aria-label="${eficaciaLabel}"><span class="status-dot"></span></span></span></span>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-analytics view-aluno-progressao" 
                                    data-aluno="${aluno.aluno_nome}"
                                    data-aluno-id="${aluno.aluno_numero}"
                                    data-intervencao="${interv.titulo}"
                                    data-cenario="${interv.cenario || ''}"
                                    data-pre-aderencia="${interv.pre?.aderencia ?? 0}"
                                    data-pre-temporalidade-inicio="${interv.pre?.temporalidade_inicio ?? 0}"
                                    data-pre-temporalidade-fim="${interv.pre?.temporalidade_fim ?? 0}"
                                    data-pre-desempenho="${interv.pre?.desempenho ?? 0}"
                                    data-pos-aderencia="${interv.pos.aderencia}"
                                    data-pos-temporalidade-inicio="${interv.pos.temporalidade_inicio}"
                                    data-pos-temporalidade-fim="${interv.pos.temporalidade_fim}"
                                    data-pos-desempenho="${interv.pos.desempenho}"
                                    data-adesao="${interv.pos.adesao}">
                                Detalhes
                            </button>
                        </td>
                    </tr>
                `;
            });
        });

        html += `</tbody></table></div>`;
        document.getElementById('alunosContent').innerHTML = html;

        // Reinicializar tooltips após renderizar tabela
        const newTooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        newTooltips.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Adicionar eventos aos botões
        document.querySelectorAll('.view-aluno-progressao').forEach(btn => {
            btn.addEventListener('click', function() {
                const dados = {
                    titulo: `${this.dataset.intervencao} - Aluno: ${this.dataset.aluno} (${this.dataset.alunoId})`,
                    aderido: this.dataset.adesao === 'Sim',
                    antes: {
                        aderencia: parseFloat(this.dataset.preAderencia),
                        temporalidadeInicio: parseFloat(this.dataset.preTemporalidadeInicio),
                        temporalidadeFim: parseFloat(this.dataset.preTemporalidadeFim),
                        desempenho: parseFloat(this.dataset.preDesempenho)
                    },
                    depois: {
                        aderencia: parseFloat(this.dataset.posAderencia),
                        temporalidadeInicio: parseFloat(this.dataset.posTemporalidadeInicio),
                        temporalidadeFim: parseFloat(this.dataset.posTemporalidadeFim),
                        desempenho: parseFloat(this.dataset.posDesempenho)
                    }
                };
                mostrarProgressao(dados);
            });
        });
    }

    // Carregar dados das intervenções
    function carregarIntervencoesPorTurma(alunosData, turmaNome) {
        const turma = turmaNome || alunosData?.turma || turmaAtual || document.getElementById('turmaFiltro')?.value || '';
        logDebug('Carregando intervenções: ' + (alunosData && alunosData.alunos ? ('alunos=' + alunosData.alunos.length) : JSON.stringify(alunosData).slice(0,200)));
        const totalAlunosTurma = (alunosData.alunos || []).length;
        // Agrupar dados por intervenção
        let intervencoesPorTurma = {};
        
        (alunosData.alunos || []).forEach(aluno => {
            (aluno.intervencoes || []).forEach(interv => {
                if (!interv?.pos) return;
                if (!intervencoesPorTurma[interv.intervencao_id]) {
                    intervencoesPorTurma[interv.intervencao_id] = {
                        intervencao_id: interv.intervencao_id,
                        titulo: interv.titulo,
                        cenario: interv.cenario || null,
                        metricas_pos: [],
                        adesoes: [],
                        total: 0
                    };
                }
                
                intervencoesPorTurma[interv.intervencao_id].metricas_pos.push({
                    aderencia: interv.pos.aderencia,
                    temporalidadeInicio: Number(interv.pos.temporalidade_inicio),
                    temporalidadeFim: Number(interv.pos.temporalidade_fim),
                    temporalidade: Number(interv.pos.temporalidade),
                    desempenho: interv.pos.desempenho,
                    adesao: interv.pos.adesao === 'Sim' ? 1 : 0
                });
                
                intervencoesPorTurma[interv.intervencao_id].adesoes.push(interv.pos.adesao === 'Sim' ? 1 : 0);
                intervencoesPorTurma[interv.intervencao_id].total++;
            });
        });
        
        let html = `<div class="table-clean-wrap">
            <table class="table table-hover table-clean table-clean-intervencoes align-middle">
                <thead>
                    <tr>
                        <th>Intervenção</th>
                        <th>Cenário</th>
                        <th>Indicadores</th>
                        <th>Resultado</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>`;
        
        Object.values(intervencoesPorTurma).forEach(interv => {
            // Calcular médias pós (apenas para quem aderiu)
            const comAdesao = interv.metricas_pos.filter((_, idx) => interv.adesoes[idx] === 1);
            const preDesempenhos = [];
            (alunosData.alunos || []).forEach(aluno => {
                const intervencaoAluno = (aluno.intervencoes || []).find(item => item.intervencao_id === interv.intervencao_id);
                if (intervencaoAluno && intervencaoAluno.pre && Number.isFinite(Number(intervencaoAluno.pre.desempenho))) {
                    preDesempenhos.push(Number(intervencaoAluno.pre.desempenho));
                }
            });
            const avgPreDesempenho = preDesempenhos.length > 0
                ? Math.round(preDesempenhos.reduce((a, b) => a + b, 0) / preDesempenhos.length)
                : -1;
            const avgAderencia = comAdesao.length > 0 ? Math.round(comAdesao.reduce((a, b) => a + b.aderencia, 0) / comAdesao.length) : 0;
            const temporalidadeInicioValidos = comAdesao
                .map(item => Number(item.temporalidadeInicio))
                .filter(valor => Number.isFinite(valor) && valor >= 0);
            const temporalidadeFimValidos = comAdesao
                .map(item => Number(item.temporalidadeFim))
                .filter(valor => Number.isFinite(valor) && valor >= 0);

            const avgTemporalidadeInicio = temporalidadeInicioValidos.length > 0
                ? Math.round(temporalidadeInicioValidos.reduce((a, b) => a + b, 0) / temporalidadeInicioValidos.length)
                : 0;
            const avgTemporalidadeFim = temporalidadeFimValidos.length > 0
                ? Math.round(temporalidadeFimValidos.reduce((a, b) => a + b, 0) / temporalidadeFimValidos.length)
                : 0;
            const avgTemporalidade = comAdesao.length > 0 ? Math.round((avgTemporalidadeInicio + avgTemporalidadeFim) / 2) : 0;
            const avgDesempenho = comAdesao.length > 0 ? Math.round(comAdesao.reduce((a, b) => a + b.desempenho, 0) / comAdesao.length) : 0;
            
            const adesaoTotal = interv.adesoes.reduce((a, b) => a + b, 0);
            const adesaoDenominador = totalAlunosTurma > 0 ? totalAlunosTurma : interv.total;
            const adesaoPercentual = adesaoDenominador > 0 ? Math.round((adesaoTotal / adesaoDenominador) * 100) : 0;
            const avaliacaoCenario = avaliarCenario(interv.cenario, {
                adesao_percentual: adesaoPercentual,
                aderencia: avgAderencia,
                temporalidade_inicio: avgTemporalidadeInicio,
                temporalidade_fim: avgTemporalidadeFim,
                desempenho: avgDesempenho,
                pre_desempenho: avgPreDesempenho
            });
            intervencoesPorTurma[interv.intervencao_id].avaliacaoCenario = avaliacaoCenario;
            
            html += `
                <tr>
                    <td>
                        <span class="cell-title">${tituloIntervencaoTabela(interv.cenario, interv.titulo)}</span>
                    </td>
                    <td>
                        <span class="cell-title">${interv.cenario || 'Não definido'}</span>
                    </td>
                    <td>
                        <div class="metric-stack compact">
                            <span class="metric-item"><span class="metric-label">Adesão</span><span class="metric-value">${adesaoTotal}/${adesaoDenominador} (${adesaoPercentual}%)</span></span>
                            <span class="metric-item"><span class="metric-label">Aderência</span><span class="metric-value">${avgAderencia}%</span></span>
                            <span class="metric-item"><span class="metric-label">Desempenho</span><span class="metric-value">${avgDesempenho}%</span></span>
                            <span class="metric-item"><span class="metric-label">Tempo</span><span class="metric-value">${avgTemporalidadeInicio} / ${avgTemporalidadeFim} min</span></span>
                        </div>
                    </td>
                    <td>
                        <span class="eficacia-label eficacia-label--${avaliacaoCenario.eficaz === null ? 'warn' : (avaliacaoCenario.eficaz ? 'ok' : 'fail')}">${avaliacaoCenario.texto}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-analytics ver-interpretacao"
                                data-intervencao-id="${interv.intervencao_id}"
                                data-turma="${turma}"
                                data-titulo="${tituloIntervencaoTabela(interv.cenario, interv.titulo)}"
                                title="Análise pedagógica com explicação de cada métrica">
                            Detalhes
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        document.getElementById('intervencoeContent').innerHTML = html;

        // Reinicializar tooltips após renderizar tabela
        const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltips.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.ver-interpretacao');
        if (btn) {
            const turma = btn.dataset.turma || document.getElementById('turmaFiltro')?.value;
            const intervencaoId = btn.dataset.intervencaoId;
            mostrarInterpretacaoIntervencao(turma, intervencaoId, btn.dataset.titulo);
        }
    });

    // Evento global para botão "Ver Progressão" de aluno (usa closest para capturar cliques em elementos internos)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.view-aluno-progressao');
        if (btn) {
            const dados = {
                titulo: `${btn.dataset.intervencao} - Aluno: ${btn.dataset.aluno} (${btn.dataset.alunoId})`,
                cenario: btn.dataset.cenario,
                aderido: btn.dataset.adesao === 'Sim',
                antes: {
                    aderencia: parseFloat(btn.dataset.preAderencia),
                    temporalidadeInicio: parseFloat(btn.dataset.preTemporalidadeInicio),
                    temporalidadeFim: parseFloat(btn.dataset.preTemporalidadeFim),
                    desempenho: parseFloat(btn.dataset.preDesempenho)
                },
                depois: {
                    aderencia: parseFloat(btn.dataset.posAderencia),
                    temporalidadeInicio: parseFloat(btn.dataset.posTemporalidadeInicio),
                    temporalidadeFim: parseFloat(btn.dataset.posTemporalidadeFim),
                    desempenho: parseFloat(btn.dataset.posDesempenho)
                }
            };
            mostrarProgressao(dados);
        }
    });

    if (intervencaoFiltro) {
        intervencaoFiltro.addEventListener('change', function() {
            intervencaoSelecionada = this.value || 'all';
            if (dadosTurmaAtual) {
                renderResultadosFiltrados(dadosTurmaAtual);
            }
        });
    }

    const initialTurma = @json($selectedTurma ?? null);
    const initialData = @json($serverAlunos ?? null);
    const initialInterpretacao = @json($interpretacaoTurma ?? null);
    const initialMetrics = @json($serverTurmaMetrics ?? null);
    if (initialTurma && initialData && Array.isArray(initialData.alunos)) {
        dadosTurmaAtual = normalizeData(initialData);
        turmaAtual = initialTurma;
        if (intervencaoFiltroWrapper) intervencaoFiltroWrapper.style.display = '';
        populateIntervencaoFiltro(dadosTurmaAtual);
        if (initialMetrics) {
            aplicarCardsDesempenhoTurma(initialMetrics);
            renderTurmaProgressaoInline(initialTurma, initialMetrics);
        }
        if (initialInterpretacao) {
            renderInterpretacaoTurma(initialInterpretacao, initialTurma);
        } else {
            mostrarInterpretacaoCarregando();
        }
        renderTabelasTurma(dadosTurmaAtual, initialTurma);
    }

    const interpPanel = document.getElementById('eficaciaInterpretacaoPanel');
    if (interpPanel && window.InterpretacaoUI?.initPopovers) {
        window.InterpretacaoUI.initPopovers(interpPanel);
    }
});
</script>
@endsection
