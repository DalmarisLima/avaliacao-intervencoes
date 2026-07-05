<?php

namespace Tests\Feature;

use App\Models\Intervencao;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResultadosApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_turma_requires_auth(): void
    {
        $this->getJson('/api/turma/1%C2%BA%20Ano%20A')
            ->assertUnauthorized();
    }

    public function test_api_turma_returns_json_for_owner(): void
    {
        $user = User::factory()->create();

        $this->seed(\Database\Seeders\TurmaSeeder::class);
        $turma = Turma::where('nome', '2º Ano A')->firstOrFail();

        $intervencao = Intervencao::create([
            'user_id' => $user->id,
            'titulo' => 'Teste API',
            'tipo_atividade' => 'Presencial',
            'descricao' => 'Desc',
            'turma' => $turma->nome,
            'turma_id' => $turma->id,
            'cenario' => 'flexivel',
            'data_inicio' => now()->subDays(5),
            'data_fim' => now()->addDays(5),
            'limiar_aderencia' => 25,
            'limiar_temporalidade_inicio' => 20,
            'limiar_temporalidade_fim' => 60,
            'limiar_desempenho' => 25,
        ]);

        app(\App\Services\SyntheticEvaluationGenerator::class)->gerar($intervencao->fresh());

        Cache::flush();

        $response = $this->actingAs($user)
            ->getJson('/api/turma/'.urlencode('2º Ano A').'/alunos');

        $response->assertOk()
            ->assertJsonStructure(['turma', 'alunos']);

        $intervencaoId = $intervencao->id;

        $this->actingAs($user)
            ->getJson(route('api.intervencao-analise', [
                'turma' => $turma->nome,
                'intervencao' => $intervencaoId,
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'titulo',
                'veredito',
                'insight_principal',
                'metricas',
                'recomendacoes',
            ]);

        $stats = $this->actingAs($user)
            ->getJson(route('api.turma-stats', ['turma' => $turma->nome]))
            ->assertOk()
            ->json();

        $interpretacao = $this->actingAs($user)
            ->getJson(route('api.turma-interpretacao', ['turma' => $turma->nome]))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('pre_desempenho', $interpretacao, json_encode($interpretacao, JSON_UNESCAPED_UNICODE));

        $this->assertArrayHasKey('interpretacao', $stats);
        $this->assertSame(
            (int) $stats['pre']['desempenho'],
            (int) $stats['interpretacao']['pre_desempenho'],
            'Pré embutido na API de stats deve coincidir'
        );
        $this->assertSame(
            (int) $stats['pos']['desempenho'],
            (int) $stats['interpretacao']['pos_desempenho'],
            'Pós embutido na API de stats deve coincidir'
        );

        $statsAll = $this->actingAs($user)
            ->getJson(route('api.turma-stats', ['turma' => $turma->nome, 'intervencao' => 'all']))
            ->assertOk()
            ->json();

        $this->assertSame(
            (int) $stats['pre']['desempenho'],
            (int) $statsAll['pre']['desempenho'],
            'intervencao=all deve retornar as mesmas métricas da turma inteira'
        );
        $this->assertSame(
            (int) $stats['pos']['desempenho'],
            (int) $statsAll['pos']['desempenho'],
            'intervencao=all deve retornar as mesmas métricas da turma inteira'
        );
    }
}
