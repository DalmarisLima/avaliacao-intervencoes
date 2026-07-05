<?php

namespace Tests\Feature;

use App\Models\Intervencao;
use App\Models\User;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntervencoesRegenerarDadosSinteticosCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_regenera_dados_por_turma(): void
    {
        $user = User::factory()->create();
        $generator = app(SyntheticEvaluationGenerator::class);

        $flexivel = Intervencao::create([
            'user_id' => $user->id,
            'titulo' => 'Flexível',
            'tipo_atividade' => 'Online',
            'descricao' => 'Desc',
            'turma' => '2º Ano A',
            'cenario' => 'flexivel',
            'data_inicio' => now()->subDays(10),
            'data_fim' => now()->addDays(10),
            'limiar_aderencia' => 25,
            'limiar_temporalidade_inicio' => 20,
            'limiar_temporalidade_fim' => 60,
            'limiar_desempenho' => 25,
        ]);

        $generator->gerar($flexivel);

        $this->assertEquals(20, $flexivel->avaliacoes()->where('tipo', 'pos')->count());

        $this->artisan('intervencoes:regenerar-dados-sinteticos', [
            '--turma' => '2º Ano A',
            '--force' => true,
        ])->assertSuccessful();

        $flexivel->refresh();

        $this->assertEquals(20, $flexivel->avaliacoes()->where('tipo', 'pos')->count());
        $this->assertNotNull($flexivel->dados_gerados_at);

        $desempenho = (int) round($flexivel->avaliacoes()
            ->where('tipo', 'pos')
            ->where('adesao', 1)
            ->avg('desempenho'));
        $this->assertSame(68, $desempenho);
    }
}
