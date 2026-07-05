<?php

namespace Tests\Unit;

use App\Services\CenarioService;
use App\Services\EficaciaInterpretacaoService;
use PHPUnit\Framework\TestCase;

class EficaciaInterpretacaoServiceTest extends TestCase
{
    private EficaciaInterpretacaoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EficaciaInterpretacaoService(new CenarioService);
    }

    public function test_nao_eficaz_sem_ganho_desempenho(): void
    {
        $result = $this->service->interpretar('flexivel', [
            'adesao_percentual' => 80,
            'aderencia' => 90,
            'temporalidade_inicio' => 10,
            'temporalidade_fim' => 20,
            'desempenho' => 50,
            'pre_desempenho' => 55,
        ], [
            'aderencia' => 25,
            'temporalidade_inicio' => 20,
            'temporalidade_fim' => 60,
            'desempenho' => 25,
        ]);

        $this->assertFalse($result['eficaz']);
        $this->assertSame('Não eficaz', $result['classificacao_rotulo']);
        $this->assertSame(0, $result['indice_eficacia']);
        $this->assertSame('erro', $result['camadas'][0]['status']);
    }

    public function test_eficaz_com_ganho_e_cenario_flexivel(): void
    {
        $result = $this->service->interpretar('flexivel', [
            'adesao_percentual' => 100,
            'aderencia' => 70,
            'temporalidade_inicio' => 10,
            'temporalidade_fim' => 30,
            'desempenho' => 70,
            'pre_desempenho' => 40,
        ], [
            'aderencia' => 25,
            'temporalidade_inicio' => 20,
            'temporalidade_fim' => 60,
            'desempenho' => 25,
        ]);

        $this->assertTrue($result['eficaz']);
        $this->assertSame('Eficaz', $result['classificacao_rotulo']);
        $this->assertSame(30, $result['delta_desempenho']);
        $this->assertGreaterThan(0, $result['indice_eficacia']);
    }

    public function test_sem_relevancia_sem_adesao(): void
    {
        $result = $this->service->interpretar('moderado', [
            'adesao_percentual' => 0,
            'aderencia' => 0,
            'temporalidade_inicio' => 0,
            'temporalidade_fim' => 0,
            'desempenho' => 0,
            'pre_desempenho' => 50,
        ]);

        $this->assertNull($result['eficaz']);
        $this->assertSame('Sem participação', $result['classificacao_rotulo']);
    }
}
