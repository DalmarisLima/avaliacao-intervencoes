<?php

namespace Tests\Feature;

use App\Models\Intervencao;
use App\Models\User;
use App\Services\SyntheticEvaluationGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SyntheticEvaluationGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_gera_avaliacoes_pre_e_pos(): void
    {
        $user = User::factory()->create();
        $intervencao = Intervencao::create([
            'user_id' => $user->id,
            'titulo' => 'Teste',
            'tipo_atividade' => 'Presencial',
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

        $generator = app(SyntheticEvaluationGenerator::class);
        $generator->gerar($intervencao);

        $intervencao->refresh();

        $this->assertNotNull($intervencao->dados_gerados_at);
        $this->assertDatabaseHas('avaliacoes', [
            'intervencao_id' => $intervencao->id,
            'tipo' => 'pre',
        ]);
        $this->assertDatabaseHas('avaliacoes', [
            'intervencao_id' => $intervencao->id,
            'tipo' => 'pos',
        ]);
        $this->assertEquals(
            20,
            $intervencao->avaliacoes()->where('tipo', 'pos')->count()
        );
    }

    public function test_gera_dataset_por_cenario(): void
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

        $dificil = Intervencao::create([
            'user_id' => $user->id,
            'titulo' => 'Difícil',
            'tipo_atividade' => 'Online',
            'descricao' => 'Desc',
            'turma' => '2º Ano A',
            'cenario' => 'dificil',
            'data_inicio' => now()->subDays(10),
            'data_fim' => now()->addDays(10),
            'limiar_aderencia' => 80,
            'limiar_temporalidade_inicio' => 10,
            'limiar_temporalidade_fim' => 30,
            'limiar_desempenho' => 80,
        ]);

        $generator->gerar($flexivel);
        $generator->gerar($dificil);

        $adesaoFlex = $flexivel->avaliacoes()->where('tipo', 'pos')->where('adesao', 1)->count();
        $adesaoDificil = $dificil->avaliacoes()->where('tipo', 'pos')->where('adesao', 1)->count();

        $this->assertSame(12, $adesaoFlex);
        $this->assertSame(18, $adesaoDificil);

        $desempenhoFlex = (int) round($flexivel->avaliacoes()
            ->where('tipo', 'pos')
            ->where('adesao', 1)
            ->avg('desempenho'));
        $desempenhoDificil = (int) round($dificil->avaliacoes()
            ->where('tipo', 'pos')
            ->where('adesao', 1)
            ->avg('desempenho'));

        $this->assertSame(68, $desempenhoFlex);
        $this->assertSame(42, $desempenhoDificil);
    }

    public function test_nao_permite_gerar_duas_vezes(): void
    {
        $user = User::factory()->create();
        $intervencao = Intervencao::create([
            'user_id' => $user->id,
            'titulo' => 'Teste',
            'tipo_atividade' => 'Online',
            'descricao' => 'Desc',
            'turma' => 'Turma Reforço',
            'cenario' => 'moderado',
            'data_inicio' => now()->subDays(5),
            'data_fim' => now()->addDays(5),
            'limiar_aderencia' => 60,
            'limiar_temporalidade_inicio' => 15,
            'limiar_temporalidade_fim' => 45,
            'limiar_desempenho' => 60,
            'dados_gerados_at' => now(),
        ]);

        $generator = app(SyntheticEvaluationGenerator::class);

        $this->expectException(ValidationException::class);
        $generator->gerar($intervencao);
    }
}
