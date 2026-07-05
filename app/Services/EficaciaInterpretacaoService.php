<?php

namespace App\Services;

/**
 * Gera interpretação pedagógica da eficácia (modelo hierárquico: desempenho → limiares → cenário).
 */
class EficaciaInterpretacaoService
{
    public function __construct(
        private readonly CenarioService $cenarioService,
    ) {}

    /**
     * @param  array<string, mixed>  $metricas
     * @param  array<string, int>  $limiares
     * @return array<string, mixed>
     */
    public function interpretar(?string $cenario, array $metricas, array $limiares = [], ?string $titulo = null): array
    {
        $avaliacao = $this->cenarioService->avaliarEficacia($cenario, $metricas, $limiares);

        $preDesempenho = (int) ($metricas['pre_desempenho'] ?? -1);
        $posDesempenho = (int) ($metricas['desempenho'] ?? 0);
        $adesaoPercentual = (int) ($metricas['adesao_percentual'] ?? 0);
        $aderencia = (int) ($metricas['aderencia'] ?? 0);
        $tempInicio = (int) ($metricas['temporalidade_inicio'] ?? 0);
        $tempFim = (int) ($metricas['temporalidade_fim'] ?? 0);

        $limiarAderencia = (int) ($limiares['aderencia'] ?? 25);
        $limiarTempInicio = (int) ($limiares['temporalidade_inicio'] ?? 20);
        $limiarTempFim = (int) ($limiares['temporalidade_fim'] ?? 60);
        $limiarDesempenho = (int) ($limiares['desempenho'] ?? 25);

        $temPre = $preDesempenho >= 0;
        $deltaDesempenho = $temPre ? $posDesempenho - $preDesempenho : null;
        $houveGanho = $temPre && $deltaDesempenho > 0;

        $camadaAprendizagem = $this->camadaAprendizagem($preDesempenho, $posDesempenho, $deltaDesempenho, $temPre);
        $camadaProcesso = $this->camadaProcesso($adesaoPercentual, $aderencia, $tempInicio, $tempFim, $limiarAderencia, $limiarTempInicio, $limiarTempFim);
        $camadaMeta = $this->camadaMeta($avaliacao, $limiarDesempenho, $houveGanho);

        $indice = $this->calcularIndiceEficacia(
            $houveGanho,
            $deltaDesempenho,
            $adesaoPercentual,
            $aderencia,
            $tempInicio,
            $tempFim,
            $posDesempenho,
            $limiarAderencia,
            $limiarTempInicio,
            $limiarTempFim,
            $limiarDesempenho,
            $avaliacao['eficaz'] === true
        );

        return [
            'titulo' => $titulo,
            'classificacao' => $this->classificacaoInterna($avaliacao['eficaz'], $avaliacao['tem_resultado']),
            'classificacao_rotulo' => $avaliacao['texto'],
            'eficaz' => $avaliacao['eficaz'],
            'tem_resultado' => $avaliacao['tem_resultado'],
            'cenario_configurado' => $avaliacao['cenario'],
            'cenario_resultado' => $avaliacao['cenario_resultado'],
            'pre_desempenho' => $temPre ? $preDesempenho : null,
            'pos_desempenho' => $posDesempenho,
            'delta_desempenho' => $deltaDesempenho,
            'houve_ganho_desempenho' => $houveGanho,
            'indice_eficacia' => $indice,
            'camadas' => [
                $camadaAprendizagem,
                $camadaProcesso,
                $camadaMeta,
            ],
            'sintese' => $this->montarSintese($titulo, $avaliacao, $camadaAprendizagem, $camadaProcesso, $camadaMeta),
            'criterios' => $this->checklistCriterios(
                $adesaoPercentual,
                $houveGanho,
                $aderencia,
                $tempInicio,
                $tempFim,
                $posDesempenho,
                $limiarAderencia,
                $limiarTempInicio,
                $limiarTempFim,
                $limiarDesempenho,
                $avaliacao
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $intervencoes  itens de intervencoesPorTurma
     * @return array<string, mixed>
     */
    public function interpretarTurma(string $turma, array $intervencoes, int $mediaPreDesempenho, int $mediaPosDesempenho): array
    {
        $interpretacoes = [];
        $totalEficazes = 0;
        $totalNaoEficazes = 0;
        $totalSemRelevancia = 0;

        foreach ($intervencoes as $item) {
            $limiares = [
                'aderencia' => (int) ($item['limiar_aderencia'] ?? $item['cenario_perfil']['aderencia'] ?? 25),
                'temporalidade_inicio' => (int) ($item['limiar_temporalidade_inicio'] ?? $item['cenario_perfil']['temporalidade_inicio'] ?? 20),
                'temporalidade_fim' => (int) ($item['limiar_temporalidade_fim'] ?? $item['cenario_perfil']['temporalidade_fim'] ?? 60),
                'desempenho' => (int) ($item['limiar_desempenho'] ?? $item['cenario_perfil']['desempenho'] ?? 25),
            ];

            $adesaoStr = $item['pos']['adesao'] ?? '0/0';
            $parts = explode('/', (string) $adesaoStr);
            $adesaoTotal = (int) ($parts[0] ?? 0);
            $adesaoDenom = (int) ($parts[1] ?? 0);
            $adesaoPercentual = $adesaoDenom > 0 ? (int) round(($adesaoTotal / $adesaoDenom) * 100) : 0;

            $interp = $this->interpretar(
                $item['cenario_normalizado'] ?? $item['cenario'] ?? null,
                [
                    'adesao_percentual' => $adesaoPercentual,
                    'aderencia' => (int) ($item['pos']['aderencia'] ?? 0),
                    'temporalidade_inicio' => (int) ($item['pos']['temporalidade_inicio'] ?? 0),
                    'temporalidade_fim' => (int) ($item['pos']['temporalidade_fim'] ?? 0),
                    'desempenho' => (int) ($item['pos']['desempenho'] ?? 0),
                    'pre_desempenho' => (int) ($item['pre']['desempenho'] ?? -1),
                ],
                $limiares,
                $item['titulo'] ?? null
            );

            $interpretacoes[] = $interp;

            if ($interp['eficaz'] === null) {
                $totalSemRelevancia++;
            } elseif ($interp['eficaz']) {
                $totalEficazes++;
            } else {
                $totalNaoEficazes++;
            }
        }

        $deltaTurma = $mediaPosDesempenho - $mediaPreDesempenho;
        $ganhoTurma = $deltaTurma > 0;
        $totalComResultado = $totalEficazes + $totalNaoEficazes;

        $turmaEficaz = null;
        $rotuloTurma = 'Sem participação';
        if ($totalComResultado > 0) {
            $turmaEficaz = $totalEficazes === $totalComResultado && $ganhoTurma;
            $rotuloTurma = $turmaEficaz ? 'Com eficácia' : 'Sem eficácia';
        }

        $camadaTurmaAprendizagem = [
            'titulo' => 'Aprendizagem na turma (desempenho)',
            'status' => $ganhoTurma ? 'ok' : ($mediaPreDesempenho >= 0 ? 'erro' : 'neutro'),
            'texto' => $ganhoTurma
                ? "A turma apresentou ganho médio de desempenho de {$deltaTurma} pontos (de {$mediaPreDesempenho}% no pré para {$mediaPosDesempenho}% no pós), indicando progresso de aprendizagem entre as intervenções analisadas."
                : ($mediaPreDesempenho >= 0
                    ? "A turma não apresentou ganho médio de desempenho (pré: {$mediaPreDesempenho}%, pós: {$mediaPosDesempenho}%). Sem avanço na métrica principal, a intervenção não é considerada eficaz para aprendizagem."
                    : 'Não há média pré disponível para calcular o ganho de desempenho da turma.'),
        ];

        $camadaTurmaIntervencoes = [
            'titulo' => 'Intervenções avaliadas',
            'status' => $turmaEficaz ? 'ok' : ($totalComResultado > 0 ? 'alerta' : 'neutro'),
            'texto' => $totalComResultado > 0
                ? "Das intervenções com participação dos alunos, {$totalEficazes} foram eficazes e {$totalNaoEficazes} não atingiram a meta, conforme os critérios de cada cenário configurado."
                : 'Nenhuma intervenção da turma possui adesão suficiente para avaliação de eficácia.',
        ];

        if ($totalSemRelevancia > 0) {
            $camadaTurmaIntervencoes['texto'] .= " {$totalSemRelevancia} intervenção(ões) não puderam ser avaliadas por falta de participação dos alunos.";
        }

        $sinteseTurma = $this->montarSinteseTurma(
            $turma,
            $rotuloTurma,
            $ganhoTurma,
            $deltaTurma,
            $totalEficazes,
            $totalComResultado,
            $totalSemRelevancia
        );

        return [
            'turma' => $turma,
            'classificacao_rotulo' => $rotuloTurma,
            'eficaz' => $turmaEficaz,
            'pre_desempenho' => $mediaPreDesempenho,
            'pos_desempenho' => $mediaPosDesempenho,
            'delta_desempenho' => $deltaTurma,
            'total_eficazes' => $totalEficazes,
            'total_nao_eficazes' => $totalNaoEficazes,
            'total_sem_relevancia' => $totalSemRelevancia,
            'camadas' => [
                $camadaTurmaAprendizagem,
                $camadaTurmaIntervencoes,
            ],
            'sintese' => $sinteseTurma,
            'intervencoes' => $interpretacoes,
        ];
    }

    /**
     * @return array{titulo: string, status: string, texto: string}
     */
    private function camadaAprendizagem(int $pre, int $pos, ?int $delta, bool $temPre): array
    {
        if (! $temPre) {
            return [
                'titulo' => 'Aprendizagem (desempenho)',
                'status' => 'neutro',
                'texto' => 'Não foi possível comparar com a avaliação pré. O desempenho pós registrado foi '.$pos.'%.',
            ];
        }

        if ($delta > 0) {
            return [
                'titulo' => 'Aprendizagem (desempenho)',
                'status' => 'ok',
                'texto' => "Houve ganho de aprendizagem: o desempenho passou de {$pre}% (antes) para {$pos}% (depois), um avanço de {$delta} pontos. Esta é a condição principal para considerar a intervenção pedagógica.",
            ];
        }

        if ($delta === 0) {
            return [
                'titulo' => 'Aprendizagem (desempenho)',
                'status' => 'erro',
                'texto' => "Não houve ganho de desempenho: a média permaneceu em {$pos}% (igual ao pré). Sem avanço na aprendizagem, a intervenção pedagógica não é considerada eficaz.",
            ];
        }

        return [
            'titulo' => 'Aprendizagem (desempenho)',
            'status' => 'erro',
            'texto' => "Houve queda de desempenho: de {$pre}% (antes) para {$pos}% (depois). A intervenção não é eficaz para a aprendizagem dos alunos, mesmo que outros indicadores pareçam favoráveis.",
        ];
    }

    /**
     * @return array{titulo: string, status: string, texto: string}
     */
    private function camadaProcesso(int $adesaoPct, int $aderencia, int $tempInicio, int $tempFim, int $limAderencia, int $limTempIni, int $limTempFim): array
    {
        if ($adesaoPct <= 0) {
            return [
                'titulo' => 'Processo pedagógico',
                'status' => 'neutro',
                'texto' => 'Nenhum aluno participou da intervenção; não há dados de execução (aderência e tempo) para interpretar.',
            ];
        }

        $aderenciaOk = $aderencia >= $limAderencia;
        $tempIniOk = $tempInicio <= $limTempIni;
        $tempFimOk = $tempFim <= $limTempFim;
        $todosOk = $aderenciaOk && $tempIniOk && $tempFimOk;

        $partes = [];
        $partes[] = $aderenciaOk
            ? "aderência de {$aderencia}% (meta: pelo menos {$limAderencia}%)"
            : "aderência de {$aderencia}% abaixo da meta de {$limAderencia}%";
        $partes[] = $tempIniOk
            ? "início em {$tempInicio} min (limite: até {$limTempIni} min)"
            : "início em {$tempInicio} min acima do limite de {$limTempIni} min";
        $partes[] = $tempFimOk
            ? "finalização em {$tempFim} min (limite: até {$limTempFim} min)"
            : "finalização em {$tempFim} min acima do limite de {$limTempFim} min";

        return [
            'titulo' => 'Processo pedagógico',
            'status' => $todosOk ? 'ok' : 'alerta',
            'texto' => 'Com '.$adesaoPct.'% de adesão na turma, o processo apresentou: '.implode('; ', $partes).'. Esses indicadores mostram como os alunos participaram e executaram a atividade.',
        ];
    }

    /**
     * @param  array<string, mixed>  $avaliacao
     * @return array{titulo: string, status: string, texto: string}
     */
    private function camadaMeta(array $avaliacao, int $limiarDesempenho, bool $houveGanho): array
    {
        if (! $avaliacao['tem_resultado']) {
            return [
                'titulo' => 'Meta do cenário',
                'status' => 'neutro',
                'texto' => 'Sem participação dos alunos, a meta do cenário não pode ser verificada.',
            ];
        }

        if (! $houveGanho) {
            return [
                'titulo' => 'Meta do cenário',
                'status' => 'erro',
                'texto' => 'Mesmo com outros indicadores aceitáveis, a meta de aprendizagem (ganho em desempenho) não foi atingida. O cenário '.$avaliacao['cenario'].' exige progresso no desempenho dos alunos.',
            ];
        }

        $atingiu = $avaliacao['eficaz'] === true;

        return [
            'titulo' => 'Meta do cenário',
            'status' => $atingiu ? 'ok' : 'alerta',
            'texto' => $atingiu
                ? "O resultado ({$avaliacao['cenario_resultado']}) atende ao cenário {$avaliacao['cenario']}, com desempenho pós de pelo menos {$limiarDesempenho}% e avanço em relação à avaliação pré."
                : "Houve algum avanço no desempenho, mas o nível ({$avaliacao['cenario_resultado']}) não atende ao cenário {$avaliacao['cenario']} nem ao desempenho mínimo de {$limiarDesempenho}% definido.",
        ];
    }

    /**
     * @param  array{titulo: string, status: string, texto: string}  $camadaAprendizagem
     * @param  array{titulo: string, status: string, texto: string}  $camadaProcesso
     * @param  array{titulo: string, status: string, texto: string}  $camadaMeta
     */
    private function montarSintese(?string $titulo, array $avaliacao, array $camadaAprendizagem, array $camadaProcesso, array $camadaMeta): string
    {
        $prefixo = $titulo ? "Para a intervenção «{$titulo}»: " : '';

        if (! $avaliacao['tem_resultado']) {
            return $prefixo.'não há base para avaliar a eficácia porque nenhum aluno participou da intervenção.';
        }

        if ($avaliacao['eficaz'] === true) {
            return $prefixo.'a intervenção é **eficaz**: houve ganho de desempenho na aprendizagem e os indicadores pós atingiram a meta do cenário '.$avaliacao['cenario'].'.';
        }

        if ($camadaAprendizagem['status'] === 'erro') {
            return $prefixo.'a intervenção é **não eficaz** porque não houve ganho de desempenho em relação à avaliação pré — o principal critério de aprendizagem.';
        }

        return $prefixo.'a intervenção é **não eficaz** porque, embora tenha havido algum ganho, o resultado não atingiu os critérios do cenário '.$avaliacao['cenario'].' configurado.';
    }

    private function montarSinteseTurma(
        string $turma,
        string $rotulo,
        bool $ganhoTurma,
        int $delta,
        int $eficazes,
        int $totalComResultado,
        int $semRelevancia
    ): string {
        if ($totalComResultado === 0) {
            return "Na turma {$turma}, não há intervenções com participação suficiente para concluir sobre eficácia.".($semRelevancia > 0 ? " ({$semRelevancia} sem dados de participação.)" : '');
        }

        if ($rotulo === 'Com eficácia') {
            return "Na turma {$turma}, as intervenções pedagógicas têm eficácia geral: ganho médio de {$delta} pontos em desempenho e todas as {$eficazes} intervenção(ões) avaliáveis atingiram os critérios do cenário.";
        }

        if (! $ganhoTurma) {
            return "Na turma {$turma}, não houve ganho médio de desempenho entre pré e pós; por isso o conjunto é classificado como sem eficácia para aprendizagem, mesmo que outras métricas de processo tenham sido favoráveis.";
        }

        return "Na turma {$turma}, houve ganho médio de desempenho ({$delta} pontos), porém apenas {$eficazes} de {$totalComResultado} intervenção(ões) atingiram integralmente os limiares do cenário — resultado geral: sem eficácia.";
    }

    private function classificacaoInterna(?bool $eficaz, bool $temResultado): string
    {
        if (! $temResultado || $eficaz === null) {
            return 'sem_relevancia';
        }

        return $eficaz ? 'eficaz' : 'nao_eficaz';
    }

    private function calcularIndiceEficacia(
        bool $houveGanho,
        ?int $delta,
        int $adesaoPct,
        int $aderencia,
        int $tempInicio,
        int $tempFim,
        int $posDesempenho,
        int $limAderencia,
        int $limTempIni,
        int $limTempFim,
        int $limDesempenho,
        bool $atingiuCenario
    ): int {
        if (! $houveGanho) {
            return 0;
        }

        $fGanho = min(1, max(0, ($delta ?? 0) / 30));
        $fAdesao = min(1, $adesaoPct / 100);
        $fAderencia = min(1, $limAderencia > 0 ? $aderencia / $limAderencia : 0);
        $fTemp = (
            min(1, $limTempIni > 0 ? $limTempIni / max(1, $tempInicio) : 0)
            + min(1, $limTempFim > 0 ? $limTempFim / max(1, $tempFim) : 0)
        ) / 2;
        $fDesempenho = min(1, $limDesempenho > 0 ? $posDesempenho / $limDesempenho : 0);

        $indice = (int) round(50 * $fGanho + 10 * $fAdesao + 15 * $fAderencia + 15 * $fTemp + 10 * $fDesempenho);

        if ($atingiuCenario) {
            $indice = min(100, $indice + 10);
        }

        return max(0, min(100, $indice));
    }

    /**
     * @param  array<string, mixed>  $avaliacao
     * @return list<array{label: string, atende: bool, detalhe: string}>
     */
    private function checklistCriterios(
        int $adesaoPct,
        bool $houveGanho,
        int $aderencia,
        int $tempInicio,
        int $tempFim,
        int $posDesempenho,
        int $limAderencia,
        int $limTempIni,
        int $limTempFim,
        int $limDesempenho,
        array $avaliacao
    ): array {
        return [
            [
                'label' => 'Há participação (adesão)',
                'atende' => $adesaoPct > 0,
                'detalhe' => $adesaoPct > 0 ? "{$adesaoPct}% dos alunos participaram" : 'Nenhum aluno participou',
            ],
            [
                'label' => 'Ganho em desempenho (pré → pós)',
                'atende' => $houveGanho,
                'detalhe' => $houveGanho ? 'Desempenho pós maior que pré' : 'Sem ganho ou houve queda',
            ],
            [
                'label' => 'Aderência atinge o limiar',
                'atende' => $aderencia >= $limAderencia,
                'detalhe' => "{$aderencia}% (limiar: {$limAderencia}%)",
            ],
            [
                'label' => 'Temporalidade de início dentro do limite',
                'atende' => $tempInicio <= $limTempIni,
                'detalhe' => "{$tempInicio} min (limite: {$limTempIni} min)",
            ],
            [
                'label' => 'Temporalidade de fim dentro do limite',
                'atende' => $tempFim <= $limTempFim,
                'detalhe' => "{$tempFim} min (limite: {$limTempFim} min)",
            ],
            [
                'label' => 'Desempenho pós atinge o limiar',
                'atende' => $posDesempenho >= $limDesempenho,
                'detalhe' => "{$posDesempenho} (limiar: {$limDesempenho})",
            ],
            [
                'label' => 'Nível compatível com o cenário escolhido',
                'atende' => $avaliacao['eficaz'] === true,
                'detalhe' => $avaliacao['cenario_resultado'].' vs cenário '.$avaliacao['cenario'],
            ],
        ];
    }
}
