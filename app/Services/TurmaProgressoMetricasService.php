<?php

namespace App\Services;

/**
 * Agrega métricas pré/pós conforme a metodologia de eficácia:
 * médias apenas sobre pares aluno×intervenção com adesão pós = Sim.
 */
class TurmaProgressoMetricasService
{
    /**
     * @param  array{alunos?: list<array<string, mixed>>}  $alunosPayload
     * @return array{
     *     pre: array{adesao: string, aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, temporalidade: int, desempenho: int},
     *     pos: array{adesao: string, aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, temporalidade: int, desempenho: int},
     *     ganhos: array{adesao: string, aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, temporalidade: int, desempenho: int},
     *     total_pares_aderentes: int
     * }
     */
    public function fromAlunosPayload(array $alunosPayload, ?int $intervencaoId = null): array
    {
        $somaPre = ['aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0, 'desempenho' => 0];
        $somaPos = ['aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'temporalidade' => 0, 'desempenho' => 0];
        $totalAderentes = 0;
        $alunosComAdesao = [];
        $totalAlunos = count($alunosPayload['alunos'] ?? []);

        foreach ($alunosPayload['alunos'] ?? [] as $aluno) {
            foreach ($aluno['intervencoes'] ?? [] as $interv) {
                if ($intervencaoId !== null && (int) ($interv['intervencao_id'] ?? 0) !== $intervencaoId) {
                    continue;
                }

                if (! isset($interv['pos'])) {
                    continue;
                }

                $aderiu = (($interv['pos']['adesao'] ?? 'Não') === 'Sim');
                if ($aderiu) {
                    $alunosComAdesao[(string) ($aluno['aluno_numero'] ?? '')] = true;
                } else {
                    continue;
                }

                $pre = $interv['pre'] ?? [];
                $pos = $interv['pos'];

                $somaPre['aderencia'] += (int) ($pre['aderencia'] ?? 0);
                $somaPre['temporalidade_inicio'] += (int) ($pre['temporalidade_inicio'] ?? 0);
                $somaPre['temporalidade_fim'] += (int) ($pre['temporalidade_fim'] ?? 0);
                $somaPre['temporalidade'] += (int) ($pre['temporalidade'] ?? 0);
                $somaPre['desempenho'] += (int) ($pre['desempenho'] ?? 0);

                $somaPos['aderencia'] += (int) ($pos['aderencia'] ?? 0);
                $somaPos['temporalidade_inicio'] += (int) ($pos['temporalidade_inicio'] ?? 0);
                $somaPos['temporalidade_fim'] += (int) ($pos['temporalidade_fim'] ?? 0);
                $somaPos['temporalidade'] += (int) ($pos['temporalidade'] ?? 0);
                $somaPos['desempenho'] += (int) ($pos['desempenho'] ?? 0);

                $totalAderentes++;
            }
        }

        $preData = $this->roundAverages($somaPre, $totalAderentes);
        $preData['adesao'] = 'N/A';

        $posData = $this->roundAverages($somaPos, $totalAderentes);
        $posData['adesao'] = $totalAlunos > 0
            ? count($alunosComAdesao).'/'.$totalAlunos
            : '0/0';

        $ganhos = [
            'adesao' => 'N/A',
            'aderencia' => $posData['aderencia'] - $preData['aderencia'],
            'temporalidade_inicio' => $posData['temporalidade_inicio'] - $preData['temporalidade_inicio'],
            'temporalidade_fim' => $posData['temporalidade_fim'] - $preData['temporalidade_fim'],
            'temporalidade' => $posData['temporalidade'] - $preData['temporalidade'],
            'desempenho' => $posData['desempenho'] - $preData['desempenho'],
        ];

        return [
            'pre' => $preData,
            'pos' => $posData,
            'ganhos' => $ganhos,
            'total_pares_aderentes' => $totalAderentes,
        ];
    }

    /**
     * Ajusta pré.desempenho (e demais pré) de cada intervenção para média só entre aderentes.
     *
     * @param  array{intervencoes: list<array<string, mixed>>}  $intervencoesPayload
     * @param  array{alunos?: list<array<string, mixed>>}  $alunosPayload
     * @return array{intervencoes: list<array<string, mixed>>}
     */
    public function aplicarPreEntreAderentesNasIntervencoes(array $intervencoesPayload, array $alunosPayload): array
    {
        $porId = [];

        foreach ($alunosPayload['alunos'] ?? [] as $aluno) {
            foreach ($aluno['intervencoes'] ?? [] as $interv) {
                if (($interv['pos']['adesao'] ?? 'Não') !== 'Sim') {
                    continue;
                }

                $id = (int) ($interv['intervencao_id'] ?? 0);
                if ($id === 0) {
                    continue;
                }

                if (! isset($porId[$id])) {
                    $porId[$id] = [
                        'aderencia' => 0,
                        'temporalidade_inicio' => 0,
                        'temporalidade_fim' => 0,
                        'temporalidade' => 0,
                        'desempenho' => 0,
                        'n' => 0,
                    ];
                }

                $pre = $interv['pre'] ?? [];
                $porId[$id]['aderencia'] += (int) ($pre['aderencia'] ?? 0);
                $porId[$id]['temporalidade_inicio'] += (int) ($pre['temporalidade_inicio'] ?? 0);
                $porId[$id]['temporalidade_fim'] += (int) ($pre['temporalidade_fim'] ?? 0);
                $porId[$id]['temporalidade'] += (int) ($pre['temporalidade'] ?? 0);
                $porId[$id]['desempenho'] += (int) ($pre['desempenho'] ?? 0);
                $porId[$id]['n']++;
            }
        }

        $lista = $intervencoesPayload['intervencoes'] ?? [];
        foreach ($lista as $indice => $item) {
            $id = (int) ($item['id'] ?? 0);
            $agg = $porId[$id] ?? null;

            if ($agg === null || $agg['n'] === 0) {
                continue;
            }

            $n = $agg['n'];
            $lista[$indice]['pre'] = [
                'aderencia' => (int) round($agg['aderencia'] / $n),
                'temporalidade_inicio' => (int) round($agg['temporalidade_inicio'] / $n),
                'temporalidade_fim' => (int) round($agg['temporalidade_fim'] / $n),
                'temporalidade' => (int) round($agg['temporalidade'] / $n),
                'desempenho' => (int) round($agg['desempenho'] / $n),
            ];
        }
        $intervencoesPayload['intervencoes'] = $lista;

        return $intervencoesPayload;
    }

    /**
     * @param  array<string, int>  $soma
     * @return array{aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, temporalidade: int, desempenho: int}
     */
    private function roundAverages(array $soma, int $total): array
    {
        if ($total === 0) {
            return [
                'aderencia' => 0,
                'temporalidade_inicio' => 0,
                'temporalidade_fim' => 0,
                'temporalidade' => 0,
                'desempenho' => 0,
            ];
        }

        return [
            'aderencia' => (int) round($soma['aderencia'] / $total),
            'temporalidade_inicio' => (int) round($soma['temporalidade_inicio'] / $total),
            'temporalidade_fim' => (int) round($soma['temporalidade_fim'] / $total),
            'temporalidade' => (int) round($soma['temporalidade'] / $total),
            'desempenho' => (int) round($soma['desempenho'] / $total),
        ];
    }
}
