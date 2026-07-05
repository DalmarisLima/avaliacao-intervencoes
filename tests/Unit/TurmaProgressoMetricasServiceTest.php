<?php

namespace Tests\Unit;

use App\Services\TurmaProgressoMetricasService;
use PHPUnit\Framework\TestCase;

class TurmaProgressoMetricasServiceTest extends TestCase
{
    private TurmaProgressoMetricasService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TurmaProgressoMetricasService;
    }

    public function test_media_desempenho_somente_entre_aderentes(): void
    {
        $payload = [
            'alunos' => [
                [
                    'aluno_numero' => 1,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 10,
                            'pre' => ['desempenho' => 40, 'aderencia' => 50, 'temporalidade_inicio' => 10, 'temporalidade_fim' => 20, 'temporalidade' => 15],
                            'pos' => ['adesao' => 'Sim', 'desempenho' => 60, 'aderencia' => 70, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 18, 'temporalidade' => 15],
                        ],
                    ],
                ],
                [
                    'aluno_numero' => 2,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 10,
                            'pre' => ['desempenho' => 80, 'aderencia' => 90, 'temporalidade_inicio' => 5, 'temporalidade_fim' => 5, 'temporalidade' => 5],
                            'pos' => ['adesao' => 'Não', 'desempenho' => 90, 'aderencia' => 90, 'temporalidade_inicio' => 5, 'temporalidade_fim' => 5, 'temporalidade' => 15],
                        ],
                    ],
                ],
                [
                    'aluno_numero' => 3,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 10,
                            'pre' => ['desempenho' => 50, 'aderencia' => 60, 'temporalidade_inicio' => 8, 'temporalidade_fim' => 12, 'temporalidade' => 10],
                            'pos' => ['adesao' => 'Sim', 'desempenho' => 70, 'aderencia' => 80, 'temporalidade_inicio' => 9, 'temporalidade_fim' => 11, 'temporalidade' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $metricas = $this->service->fromAlunosPayload($payload);

        $this->assertSame(2, $metricas['total_pares_aderentes']);
        $this->assertSame(45, $metricas['pre']['desempenho']);
        $this->assertSame(65, $metricas['pos']['desempenho']);
        $this->assertSame(20, $metricas['ganhos']['desempenho']);
        $this->assertSame('2/3', $metricas['pos']['adesao']);
    }

    public function test_filtro_por_intervencao(): void
    {
        $payload = [
            'alunos' => [
                [
                    'aluno_numero' => 1,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 1,
                            'pre' => ['desempenho' => 30, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                            'pos' => ['adesao' => 'Sim', 'desempenho' => 50, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                        ],
                        [
                            'intervencao_id' => 2,
                            'pre' => ['desempenho' => 90, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                            'pos' => ['adesao' => 'Sim', 'desempenho' => 95, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                        ],
                    ],
                ],
            ],
        ];

        $metricas = $this->service->fromAlunosPayload($payload, 1);

        $this->assertSame(30, $metricas['pre']['desempenho']);
        $this->assertSame(50, $metricas['pos']['desempenho']);
    }

    public function test_pre_entre_aderentes_por_intervencao(): void
    {
        $alunos = [
            'alunos' => [
                [
                    'aluno_numero' => 1,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 5,
                            'pre' => ['desempenho' => 40, 'aderencia' => 1, 'temporalidade_inicio' => 1, 'temporalidade_fim' => 1, 'temporalidade' => 1],
                            'pos' => ['adesao' => 'Sim', 'desempenho' => 55, 'aderencia' => 1, 'temporalidade_inicio' => 1, 'temporalidade_fim' => 1, 'temporalidade' => 1],
                        ],
                    ],
                ],
                [
                    'aluno_numero' => 2,
                    'intervencoes' => [
                        [
                            'intervencao_id' => 5,
                            'pre' => ['desempenho' => 90, 'aderencia' => 1, 'temporalidade_inicio' => 1, 'temporalidade_fim' => 1, 'temporalidade' => 1],
                            'pos' => ['adesao' => 'Não', 'desempenho' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                        ],
                    ],
                ],
            ],
        ];

        $intervencoes = [
            'intervencoes' => [
                [
                    'id' => 5,
                    'pre' => ['desempenho' => 65, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                    'pos' => ['adesao' => '1/2', 'desempenho' => 55, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0],
                ],
            ],
        ];

        $result = $this->service->aplicarPreEntreAderentesNasIntervencoes($intervencoes, $alunos);

        $this->assertSame(40, $result['intervencoes'][0]['pre']['desempenho']);
    }
}
