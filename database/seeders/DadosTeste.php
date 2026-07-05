<?php

namespace Database\Seeders;

use App\Models\Intervencao;
use App\Models\Avaliacao;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

/**
 * @deprecated Usa nomenclatura antiga (1A, 1B, 2A). Prefira o fluxo sintético com turmas
 *             de config/turmas.php e datasets em database/data/turmas/*.json.
 */
class DadosTeste extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar dados antigos
        Avaliacao::truncate();
        Intervencao::truncate();

        $faker = Faker::create('pt_BR');

        // 3 turmas com estrutura clara
        $turmas = ['1A', '1B', '2A'];
        $alunos_nomes = [
            'Ana Silva', 'Bruno Costa', 'Carlos Santos', 'Diana Oliveira', 
            'Eduardo Pereira', 'Fernanda Lima', 'Gabriel Rocha', 'Helena Mendes'
        ];
        
        $intervencoesPorTurma = [
            '1A' => [
                [
                    'titulo' => 'Reforço de Leitura',
                    'descricao' => 'Intervenção para melhorar compreensão leitora',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'flexivel',
                    'aderentes' => 6,
                    'pre_values' => [45, 48, 50, 47, 46, 49],
                    'pos_values' => [82, 84, 86, 80, 79, 83],
                    'pos_temporalidade_inicio_values' => [14, 15, 13, 16, 14, 15],
                    'pos_temporalidade_fim_values' => [36, 38, 35, 40, 39, 37],
                ],
                [
                    'titulo' => 'Reforço de Escrita',
                    'descricao' => 'Desenvolvimento de habilidades de escrita',
                    'tipo_atividade' => 'Online',
                    'cenario' => 'moderado',
                    'aderentes' => 5,
                    'pre_values' => [58, 60, 62, 59, 61, 60],
                    'pos_values' => [48, 50, 52, 49, 47, 46],
                    'pos_temporalidade_inicio_values' => [26, 28, 25, 30, 29, 27],
                    'pos_temporalidade_fim_values' => [58, 62, 64, 60, 63, 59],
                ],
                [
                    'titulo' => 'Reforço de Matemática',
                    'descricao' => 'Conceitos básicos de operações',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'personalizado',
                    'aderentes' => 0,
                ],
            ],
            '1B' => [
                [
                    'titulo' => 'Reforço de Leitura',
                    'descricao' => 'Intervenção para melhorar compreensão leitora',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'moderado',
                    'aderentes' => 3,
                    'pre_values' => [65, 66, 64, 63, 62, 61],
                    'pos_values' => [40, 42, 39, 38, 37, 36],
                ],
                [
                    'titulo' => 'Reforço de Escrita',
                    'descricao' => 'Desenvolvimento de habilidades de escrita',
                    'tipo_atividade' => 'Online',
                    'cenario' => 'flexivel',
                    'aderentes' => 4,
                    'pre_values' => [72, 70, 71, 73, 69, 68],
                    'pos_values' => [45, 43, 44, 46, 42, 41],
                ],
                [
                    'titulo' => 'Reforço de Matemática',
                    'descricao' => 'Conceitos básicos de operações',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'personalizado',
                    'aderentes' => 0,
                    'pre_values' => [60, 59, 61, 58, 57, 56],
                    'pos_values' => [0, 0, 0, 0, 0, 0],
                ],
            ],
            '2A' => [
                [
                    'titulo' => 'Reforço de Leitura',
                    'descricao' => 'Intervenção para melhorar compreensão leitora',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'flexivel',
                    'aderentes' => 4,
                    'pre_values' => [60, 62, 58, 60, 59, 61],
                    'pos_values' => [61, 60, 59, 60, 58, 62],
                ],
                [
                    'titulo' => 'Reforço de Escrita',
                    'descricao' => 'Desenvolvimento de habilidades de escrita',
                    'tipo_atividade' => 'Online',
                    'cenario' => 'moderado',
                    'aderentes' => 4,
                    'pre_values' => [58, 60, 62, 60, 59, 61],
                    'pos_values' => [57, 61, 61, 61, 58, 62],
                ],
                [
                    'titulo' => 'Reforço de Matemática',
                    'descricao' => 'Conceitos básicos de operações',
                    'tipo_atividade' => 'Presencial',
                    'cenario' => 'personalizado',
                    'aderentes' => 4,
                    'pre_values' => [59, 61, 60, 60, 58, 62],
                    'pos_values' => [0, 0, 0, 0, 0, 0],
                ],
            ],
        ];

        foreach ($turmas as $turma) {
            $intervencoes = $intervencoesPorTurma[$turma] ?? [];
            
            foreach ($intervencoes as $interv_config) {
                // Criar intervenção
                $interv = Intervencao::create([
                    'titulo' => "{$interv_config['titulo']} - {$turma}",
                    'tipo_atividade' => $interv_config['tipo_atividade'],
                    'descricao' => $interv_config['descricao'],
                    'turma' => $turma,
                    'data_inicio' => now()->subDays(45),
                    'data_fim' => now()->subDays(15),
                    'link' => 'https://exemplo.com',
                ]);
                
                $total_alunos = 6;
                $alunos_aderentes = max(0, min($total_alunos, (int)($interv_config['aderentes'] ?? 5)));
                
                // Avaliações PRÉ para todos os alunos
                for ($i = 0; $i < $total_alunos; $i++) {
                    $alunoNome = $alunos_nomes[$i % count($alunos_nomes)];
                    
                    // Usar valores pré-definidos ou gerar aleatório
                    $preBase = $interv_config['pre_values'][$i] ?? rand(40, 65);
                    $preValue = max(0, min(100, $preBase));
                    $preTemporalidadeInicio = $interv_config['pre_temporalidade_inicio_values'][$i] ?? max(0, min(100, $preValue + (($i % 3) - 1) * 2));
                    $preTemporalidadeFim = $interv_config['pre_temporalidade_fim_values'][$i] ?? max(0, min(100, $preValue + (($i % 3) - 1) * 3));
                    
                    Avaliacao::create([
                        'intervencao_id' => $interv->id,
                        'cenario' => $interv_config['cenario'] ?? null,
                        'aluno_numero' => $i + 1,
                        'aluno_nome' => $alunoNome,
                        'tipo' => 'pre',
                        'adesao' => 0,
                        'aderencia' => $preValue,
                        'temporalidade_inicio' => $preTemporalidadeInicio,
                        'temporalidade_fim' => $preTemporalidadeFim,
                        'temporalidade' => (int) round(($preTemporalidadeInicio + $preTemporalidadeFim) / 2),
                        'desempenho' => $preValue,
                        'observacoes' => 'Avaliação pré-intervenção'
                    ]);
                }
                
                // Avaliações PÓS para alunos que aderiram
                for ($i = 0; $i < $alunos_aderentes; $i++) {
                    $alunoNome = $alunos_nomes[$i % count($alunos_nomes)];
                    
                    // Usar valores pós-definidos ou gerar aleatório
                    $posBase = $interv_config['pos_values'][$i] ?? rand(50, 80);
                    $posValue = max(0, min(100, $posBase));
                    $posTemporalidadeInicio = $interv_config['pos_temporalidade_inicio_values'][$i] ?? max(0, min(100, $posValue + (($i % 3) - 1) * 2));
                    $posTemporalidadeFim = $interv_config['pos_temporalidade_fim_values'][$i] ?? max(0, min(100, $posValue + (($i % 3) - 1) * 3));
                    
                    Avaliacao::create([
                        'intervencao_id' => $interv->id,
                        'cenario' => $interv_config['cenario'] ?? null,
                        'aluno_numero' => $i + 1,
                        'aluno_nome' => $alunoNome,
                        'tipo' => 'pos',
                        'adesao' => 1,
                        'aderencia' => $posValue,
                        'temporalidade_inicio' => $posTemporalidadeInicio,
                        'temporalidade_fim' => $posTemporalidadeFim,
                        'temporalidade' => (int) round(($posTemporalidadeInicio + $posTemporalidadeFim) / 2),
                        'desempenho' => $posValue,
                        'observacoes' => 'Participou da intervenção'
                    ]);
                }
                
                // Avaliações PÓS para alunos que NÃO aderiram
                for ($i = $alunos_aderentes; $i < $total_alunos; $i++) {
                    $alunoNome = $alunos_nomes[$i % count($alunos_nomes)];
                    
                    Avaliacao::create([
                        'intervencao_id' => $interv->id,
                        'cenario' => $interv_config['cenario'] ?? null,
                        'aluno_numero' => $i + 1,
                        'aluno_nome' => $alunoNome,
                        'tipo' => 'pos',
                        'adesao' => 0,
                        'aderencia' => 0,
                        'temporalidade_inicio' => 0,
                        'temporalidade_fim' => 0,
                        'temporalidade' => 0,
                        'desempenho' => 0,
                        'observacoes' => 'Não aderiu à intervenção'
                    ]);
                }
            }
        }

        $this->command->info('✓ Dados de teste inseridos com sucesso!');
        $this->command->info('');
        $this->command->info('Estrutura criada:');
        $this->command->info('  • 3 turmas: 1A, 1B, 2A');
        $this->command->info('  • 3 intervenções por turma');
        $this->command->info('  • 6 alunos por intervenção (adesão variável por turma/intervenção)');
        $this->command->info('');
        $this->command->info('Cenários de resultados:');
        $this->command->info('  ✓ 1A / Flexível: cenário atingido com eficácia');
        $this->command->info('  ✗ 1A / Moderado: cenário não atingido');
        $this->command->info('  • 1A / Personalizado: sem resultado estatístico por adesão zero');
        $this->command->info('  • Cenário personalizado usa valores variáveis no PRÉ para demonstração');
    }
}
