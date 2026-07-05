<?php

namespace Tests\Unit;

use App\Data\SyntheticDatasetRepository;
use Tests\TestCase;

class SyntheticDatasetRepositoryTest extends TestCase
{
    public function test_carrega_dataset_da_turma_1_ano_a(): void
    {
        $repo = new SyntheticDatasetRepository;
        $alunos = $repo->forTurma('1º Ano A');

        $this->assertNotEmpty($alunos);
        $this->assertArrayHasKey('nome', $alunos[0]);
        $this->assertArrayHasKey('pre', $alunos[0]);
        $this->assertArrayHasKey('pos', $alunos[0]);

        $nomes = array_column($alunos, 'nome');
        $this->assertSame(count($nomes), count(array_unique($nomes)));
    }

    public function test_carrega_dataset_por_cenario_2_ano_a(): void
    {
        $repo = new SyntheticDatasetRepository;

        $flexivel = $repo->forTurma('2º Ano A', 'flexivel');
        $dificil = $repo->forTurma('2º Ano A', 'dificil');

        $this->assertCount(20, $flexivel);
        $this->assertCount(20, $dificil);

        $adesaoFlex = count(array_filter($flexivel, fn (array $a): bool => ($a['pos']['adesao'] ?? 0) === 1));
        $adesaoDificil = count(array_filter($dificil, fn (array $a): bool => ($a['pos']['adesao'] ?? 0) === 1));

        $this->assertSame(12, $adesaoFlex);
        $this->assertSame(18, $adesaoDificil);
    }

    public function test_lista_turmas_configuradas(): void
    {
        $repo = new SyntheticDatasetRepository;
        $turmas = $repo->turmasDisponiveis();

        $this->assertContains('1º Ano A', $turmas);
        $this->assertContains('Turma Reforço', $turmas);
    }
}
