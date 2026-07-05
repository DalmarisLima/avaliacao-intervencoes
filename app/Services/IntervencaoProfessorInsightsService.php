<?php

namespace App\Services;

/**
 * Textos e insights pedagógicos para o professor (por intervenção).
 */
class IntervencaoProfessorInsightsService
{
    public function __construct(
        private readonly EficaciaInterpretacaoService $interpretacaoService,
        private readonly CenarioService $cenarioService,
    ) {}

    /**
     * @param  array<string, mixed>  $intervencao  estrutura de intervencoesPorTurma
     * @return array<string, mixed>
     */
    public function analisar(array $intervencao, int $totalAlunosTurma): array
    {
        $adesaoStr = $intervencao['pos']['adesao'] ?? '0/0';
        $parts = explode('/', (string) $adesaoStr);
        $adesaoTotal = (int) ($parts[0] ?? 0);
        $adesaoDenom = $totalAlunosTurma > 0 ? $totalAlunosTurma : (int) ($parts[1] ?? 0);
        $adesaoPercentual = $adesaoDenom > 0 ? (int) round(($adesaoTotal / $adesaoDenom) * 100) : 0;

        $limiares = [
            'aderencia' => (int) ($intervencao['limiar_aderencia'] ?? $intervencao['cenario_perfil']['aderencia'] ?? 25),
            'temporalidade_inicio' => (int) ($intervencao['limiar_temporalidade_inicio'] ?? $intervencao['cenario_perfil']['temporalidade_inicio'] ?? 20),
            'temporalidade_fim' => (int) ($intervencao['limiar_temporalidade_fim'] ?? $intervencao['cenario_perfil']['temporalidade_fim'] ?? 60),
            'desempenho' => (int) ($intervencao['limiar_desempenho'] ?? $intervencao['cenario_perfil']['desempenho'] ?? 25),
        ];

        $preDesempenho = (int) ($intervencao['pre']['desempenho'] ?? -1);
        $posDesempenho = (int) ($intervencao['pos']['desempenho'] ?? 0);

        $metricas = [
            'adesao_percentual' => $adesaoPercentual,
            'aderencia' => (int) ($intervencao['pos']['aderencia'] ?? 0),
            'temporalidade_inicio' => (int) ($intervencao['pos']['temporalidade_inicio'] ?? 0),
            'temporalidade_fim' => (int) ($intervencao['pos']['temporalidade_fim'] ?? 0),
            'desempenho' => $posDesempenho,
            'pre_desempenho' => $preDesempenho,
        ];

        $cenario = $intervencao['cenario_normalizado'] ?? $intervencao['cenario'] ?? null;
        $interpretacao = $this->interpretacaoService->interpretar(
            $cenario,
            $metricas,
            $limiares,
            $intervencao['titulo'] ?? null
        );

        $deltaDesempenho = $preDesempenho >= 0 ? $posDesempenho - $preDesempenho : null;

        return [
            'titulo' => $intervencao['titulo'] ?? 'Intervenção',
            'cenario' => $interpretacao['cenario_configurado'] ?? $interpretacao['cenario_resultado'] ?? null,
            'cenario_resultado' => $interpretacao['cenario_resultado'] ?? null,
            'veredito' => $interpretacao['classificacao_rotulo'],
            'eficaz' => $interpretacao['eficaz'],
            'insight_principal' => $this->insightPrincipal($interpretacao, $adesaoPercentual, $deltaDesempenho, $adesaoTotal, $adesaoDenom),
            'sintese' => str_replace(['**', '*'], '', $interpretacao['sintese'] ?? ''),
            'pre_desempenho' => $preDesempenho >= 0 ? $preDesempenho : null,
            'pos_desempenho' => $posDesempenho,
            'delta_desempenho' => $deltaDesempenho,
            'adesao' => [
                'total' => $adesaoTotal,
                'denominador' => $adesaoDenom,
                'percentual' => $adesaoPercentual,
                'texto' => "{$adesaoTotal} de {$adesaoDenom} alunos ({$adesaoPercentual}%)",
            ],
            'metricas' => $this->metricasExplicadas($metricas, $limiares, $preDesempenho),
            'recomendacoes' => $this->recomendacoes($interpretacao, $metricas, $limiares, $adesaoPercentual, $deltaDesempenho),
            'limiares' => $limiares,
            'perfil_cenario' => $interpretacao['perfil'] ?? $this->cenarioService->perfil($cenario),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enriquecerInterpretacaoTurma(array $interpretacaoTurma): array
    {
        $interpretacaoTurma['insight_principal'] = $this->insightTurma(
            $interpretacaoTurma['classificacao_rotulo'] ?? '',
            $interpretacaoTurma['delta_desempenho'] ?? 0,
            (int) ($interpretacaoTurma['total_eficazes'] ?? 0),
            (int) ($interpretacaoTurma['total_nao_eficazes'] ?? 0),
            (int) ($interpretacaoTurma['total_sem_relevancia'] ?? 0),
        );

        return $interpretacaoTurma;
    }

    private function insightPrincipal(array $interp, int $adesaoPct, ?int $delta, int $adesaoTotal, int $adesaoDenom): string
    {
        if (! ($interp['tem_resultado'] ?? false)) {
            return "Nenhum aluno participou desta intervenção (0 de {$adesaoDenom}). Não é possível avaliar a eficácia — vale revisar a proposta ou o momento de aplicação com a turma.";
        }

        if ($delta !== null && $delta <= 0) {
            return 'Os alunos que participaram não avançaram no desempenho em relação à avaliação pré. A intervenção pedagógica não produziu o progresso de aprendizagem esperado, mesmo que outros indicadores pareçam aceitáveis.';
        }

        if ($interp['eficaz'] === true) {
            $ganho = $delta > 0 ? " Houve ganho de {$delta} pontos no desempenho médio." : '';

            $cenario = $interp['cenario_configurado'] ?? $interp['cenario_resultado'] ?? 'definido';
            return "A intervenção atingiu a meta do cenário {$cenario}: houve participação ({$adesaoPct}% da turma), progresso na aprendizagem e indicadores dentro dos critérios definidos.{$ganho}";
        }

        $cenario = $interp['cenario_configurado'] ?? $interp['cenario_resultado'] ?? 'definido';
        return "Houve participação ({$adesaoTotal} de {$adesaoDenom} alunos) e algum avanço no desempenho, mas o conjunto ainda não atingiu o cenário {$cenario} configurado. Vale revisar conteúdo, duração ou critérios da intervenção.";
    }

    private function insightTurma(string $rotulo, int $delta, int $eficazes, int $naoEficazes, int $semRelevancia): string
    {
        if ($eficazes + $naoEficazes === 0) {
            return $semRelevancia > 0
                ? 'Nenhuma intervenção pedagógica desta turma teve participação suficiente para análise. Selecione outra turma ou revise como as intervenções foram aplicadas.'
                : 'Ainda não há intervenções com dados suficientes nesta turma.';
        }

        if ($delta <= 0) {
            return 'A turma não apresentou ganho médio de desempenho entre o momento pré e o pós da intervenção. Priorize ações que elevem a aprendizagem dos alunos.';
        }

        if ($rotulo === 'Com eficácia') {
            return "A turma avançou em desempenho (ganho médio de {$delta} pontos) e as intervenções avaliadas foram eficazes conforme os critérios que você definiu.";
        }

        return "A turma teve ganho médio de {$delta} pontos em desempenho, porém {$naoEficazes} intervenção(ões) não atingiram a meta do cenário. Use «Detalhes» em cada linha para orientar os próximos ajustes.";
    }

    /**
     * @param  array<string, mixed>  $metricas
     * @param  array<string, int>  $limiares
     * @return list<array<string, mixed>>
     */
    private function metricasExplicadas(array $metricas, array $limiares, int $preDesempenho): array
    {
        $aderencia = (int) $metricas['aderencia'];
        $tempIni = (int) $metricas['temporalidade_inicio'];
        $tempFim = (int) $metricas['temporalidade_fim'];
        $desempenho = (int) $metricas['desempenho'];
        $adesaoPct = (int) $metricas['adesao_percentual'];

        $items = [
            [
                'id' => 'adesao',
                'nome' => 'Adesão',
                'o_que_e' => 'Quantos alunos da turma aceitaram participar e iniciaram a atividade da intervenção pedagógica.',
                'valor' => "{$adesaoPct}%",
                'limiar' => 'É necessário que alunos participem para avaliar a intervenção',
                'atende' => $adesaoPct > 0,
                'insight' => $adesaoPct >= 70
                    ? 'Boa participação: a maioria da turma entrou na intervenção — os resultados representam bem a turma.'
                    : ($adesaoPct >= 40
                        ? 'Participação moderada: parte da turma ficou de fora — interprete os resultados com cautela.'
                        : ($adesaoPct > 0
                            ? 'Poucos alunos participaram; os indicadores refletem apenas esse grupo.'
                            : 'Nenhum aluno participou: não há como avaliar o impacto desta intervenção nesta turma.')),
            ],
            [
                'id' => 'aderencia',
                'nome' => 'Aderência',
                'o_que_e' => 'Entre os alunos que participaram, quanto da proposta foi realizada (tarefas concluídas com qualidade).',
                'valor' => "{$aderencia}%",
                'limiar' => "Critério do cenário: mínimo {$limiares['aderencia']}%",
                'atende' => $aderencia >= $limiares['aderencia'],
                'insight' => $aderencia >= 80
                    ? 'Ótima execução — os alunos seguiram bem a proposta da intervenção.'
                    : ($aderencia >= $limiares['aderencia']
                        ? 'Execução dentro do esperado para o cenário configurado.'
                        : 'Vários alunos participaram, mas não concluíram as tarefas como previsto — pode indicar dificuldade ou proposta longa demais.'),
            ],
            [
                'id' => 'temporalidade',
                'nome' => 'Temporalidade',
                'o_que_e' => 'Tempo médio para começar e para terminar a atividade, entre os alunos que participaram.',
                'valor' => "Início {$tempIni} min · Fim {$tempFim} min",
                'limiar' => "Até {$limiares['temporalidade_inicio']} min para iniciar e {$limiares['temporalidade_fim']} min para concluir",
                'atende' => $tempIni <= $limiares['temporalidade_inicio'] && $tempFim <= $limiares['temporalidade_fim'],
                'insight' => ($tempIni <= $limiares['temporalidade_inicio'] && $tempFim <= $limiares['temporalidade_fim'])
                    ? 'Ritmo adequado: os alunos começaram e finalizaram dentro do tempo que você definiu.'
                    : ($tempFim > $limiares['temporalidade_fim']
                        ? 'Conclusão demorada: pode indicar atividade complexa demais ou necessidade de mais apoio em sala.'
                        : 'Demora para começar: vale reforçar motivação ou deixar as instruções iniciais mais claras.'),
            ],
        ];

        $desempenhoItem = [
            'id' => 'desempenho',
            'nome' => 'Desempenho',
            'o_que_e' => 'Resultado de aprendizagem após a intervenção — é o indicador mais importante para saber se a ação funcionou.',
            'valor' => $preDesempenho >= 0 ? "Pré {$preDesempenho}% → Pós {$desempenho}%" : "Pós {$desempenho}%",
            'limiar' => "Mínimo {$limiares['desempenho']}% no pós e avanço em relação ao pré",
            'atende' => $preDesempenho >= 0 && $desempenho > $preDesempenho && $desempenho >= $limiares['desempenho'],
            'insight' => '',
        ];

        if ($preDesempenho >= 0) {
            $delta = $desempenho - $preDesempenho;
            if ($delta > 15) {
                $desempenhoItem['insight'] = "Ganho expressivo de {$delta} pontos: a intervenção contribuiu de forma clara para a aprendizagem dos alunos que participaram.";
            } elseif ($delta > 0) {
                $desempenhoItem['insight'] = "Ganho de {$delta} pontos: houve avanço, embora modesto — pode valer ajustar a intervenção para ampliar o efeito na turma.";
            } elseif ($delta === 0) {
                $desempenhoItem['insight'] = 'Desempenho estável: não houve avanço em relação ao pré — a intervenção não impactou a aprendizagem como esperado.';
            } else {
                $desempenhoItem['insight'] = 'Queda de '.abs($delta).' pontos: os resultados pioraram em relação ao pré — revise a intervenção ou o momento de aplicação.';
            }
        } else {
            $desempenhoItem['insight'] = $desempenho >= $limiares['desempenho']
                ? 'Desempenho pós dentro do critério definido no cenário.'
                : 'Desempenho pós abaixo do critério definido no cenário.';
        }

        $items[] = $desempenhoItem;

        return $items;
    }

    /**
     * @param  array<string, mixed>  $interp
     * @param  array<string, mixed>  $metricas
     * @param  array<string, int>  $limiares
     * @return list<string>
     */
    private function recomendacoes(array $interp, array $metricas, array $limiares, int $adesaoPct, ?int $delta): array
    {
        $recs = [];

        if ($adesaoPct < 40) {
            $recs[] = 'Para ampliar a participação, combine comunicação prévia com a turma, tempo em sala e valorização da atividade.';
        }

        if ($delta !== null && $delta <= 0) {
            $recs[] = 'Revise conteúdo, sequência didática ou nível de dificuldade — o desempenho dos alunos não avançou.';
            $recs[] = 'Compare com outras intervenções da turma que obtiveram ganho e identifique o que foi diferente na prática.';
        }

        if (($metricas['aderencia'] ?? 0) < $limiares['aderencia']) {
            $recs[] = 'Muitos alunos não completaram as tarefas: simplifique instruções ou divida a intervenção em etapas menores.';
        }

        if ($interp['eficaz'] !== true && ($delta ?? 0) > 0) {
            $recs[] = 'Houve avanço na aprendizagem, mas nem todos os critérios do cenário foram atingidos — ajuste a proposta ou reforce o apoio na próxima aplicação.';
        }

        if ($interp['eficaz'] === true && empty($recs)) {
            $recs[] = 'Mantenha o que funcionou nesta intervenção (clareza, duração, exigência) em novas ações com turmas semelhantes.';
        }

        if (empty($recs)) {
            $recs[] = 'Na aba «Por aluno», confira casos individuais para planejar apoio personalizado.';
        }

        return array_slice($recs, 0, 4);
    }
}
