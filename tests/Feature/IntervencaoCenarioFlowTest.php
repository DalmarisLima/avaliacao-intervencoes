<?php

namespace Tests\Feature;

use App\Models\Turma;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntervencaoCenarioFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_iniciar_cenario_redirects_to_definir_cenario_page(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\TurmaSeeder::class);

        $response = $this->actingAs($user)->post(route('intervencoes.iniciar-cenario'));

        $response->assertRedirect();
        $this->assertStringContainsString('/intervencoes/', $response->headers->get('Location') ?? '');
        $this->assertStringContainsString('/cenario', $response->headers->get('Location') ?? '');
    }

    public function test_salvar_cenario_redirects_to_resultados(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\TurmaSeeder::class);
        $turma = Turma::where('nome', '2º Ano A')->firstOrFail();

        $this->actingAs($user)->post(route('intervencoes.iniciar-cenario'));

        $intervencao = \App\Models\Intervencao::where('user_id', $user->id)->latest('id')->firstOrFail();

        $response = $this->actingAs($user)->post(
            route('intervencoes.salvar-cenario', ['intervencao' => $intervencao->id]),
            [
                'cenario' => 'flexivel',
                'limiar_aderencia' => 25,
                'limiar_temporalidade_inicio' => 20,
                'limiar_temporalidade_fim' => 60,
                'limiar_desempenho' => 25,
            ]
        );

        $response->assertRedirect(route('resultados', ['turma' => $turma->nome]));
        $this->assertDatabaseHas('intervencoes', [
            'id' => $intervencao->id,
            'cenario' => 'flexivel',
        ]);
    }

    public function test_salvar_cenario_nao_aceita_moderado(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\TurmaSeeder::class);
        $turma = Turma::where('nome', '2º Ano A')->firstOrFail();

        $this->actingAs($user)->post(route('intervencoes.iniciar-cenario'));

        $intervencao = \App\Models\Intervencao::where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->actingAs($user)
            ->post(route('intervencoes.salvar-cenario', ['intervencao' => $intervencao->id]), [
                'cenario' => 'moderado',
                'limiar_aderencia' => 60,
                'limiar_temporalidade_inicio' => 15,
                'limiar_temporalidade_fim' => 45,
                'limiar_desempenho' => 60,
            ])
            ->assertSessionHasErrors('cenario');
    }
}
