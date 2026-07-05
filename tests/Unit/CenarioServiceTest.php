<?php

namespace Tests\Unit;

use App\Enums\Cenario;
use App\Services\CenarioService;
use PHPUnit\Framework\TestCase;

class CenarioServiceTest extends TestCase
{
    private CenarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CenarioService;
    }

    public function test_normaliza_sinonimos_de_cenario(): void
    {
        $this->assertSame('flexivel', $this->service->normalizar('Leve'));
        $this->assertSame('moderado', $this->service->normalizar('Rígido'));
        $this->assertSame('dificil', $this->service->normalizar('Personalizado'));
    }

    public function test_classifica_flexivel_com_limiares_padrao(): void
    {
        $resultado = $this->service->classificarResultado([
            'adesao_percentual' => 80,
            'aderencia' => 30,
            'temporalidade_inicio' => 18,
            'temporalidade_fim' => 50,
            'desempenho' => 30,
        ]);

        $this->assertSame('flexivel', $resultado['normalizado']);
        $this->assertTrue($resultado['tem_resultado']);
    }

    public function test_titulo_tabela_experimento(): void
    {
        $this->assertSame('Cenário 1', Cenario::tituloTabela('flexivel', 'Bem-vindo(a) ao estudo — Flexível'));
        $this->assertSame('Cenário 2', Cenario::tituloTabela('dificil', 'Bem-vindo(a) ao estudo — Difícil'));
        $this->assertSame('Minha intervenção', Cenario::tituloTabela('moderado', 'Minha intervenção'));
    }

    public function test_eficacia_nula_sem_adesao(): void
    {
        $avaliacao = $this->service->avaliarEficacia('flexivel', [
            'adesao_percentual' => 0,
            'aderencia' => 0,
            'temporalidade_inicio' => 0,
            'temporalidade_fim' => 0,
            'desempenho' => 0,
        ]);

        $this->assertNull($avaliacao['eficaz']);
        $this->assertSame('Sem participação', $avaliacao['texto']);
    }
}
