<?php

namespace App\Services;

use App\Enums\Cenario;

class CenarioService
{
    public function normalizar(?string $cenario): string
    {
        if ($cenario === null || trim($cenario) === '') {
            return Cenario::Flexivel->value;
        }

        return Cenario::fromInput($cenario)->value;
    }

    public function rotulo(?string $cenario): string
    {
        if ($cenario === null || trim($cenario) === '') {
            return 'Não definido';
        }

        return Cenario::fromInput($cenario)->rotulo();
    }

    /**
     * @return array{adesao: string, aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, desempenho: int}
     */
    public function perfil(?string $cenario): array
    {
        if ($cenario === null || trim($cenario) === '') {
            return [
                'adesao' => 'sim',
                'aderencia' => 0,
                'temporalidade_inicio' => 0,
                'temporalidade_fim' => 0,
                'desempenho' => 0,
            ];
        }

        return Cenario::fromInput($cenario)->limiaresPadrao();
    }

    /**
     * Classifica o nível atingido com base nas métricas agregadas e limiares.
     *
     * @param  array<string, mixed>  $metricas
     * @param  array<string, int>  $limiares
     * @return array{normalizado: string, rotulo: string, tem_resultado: bool}
     */
    public function classificarResultado(array $metricas, array $limiares = []): array
    {
        $limiarAderencia = $limiares['aderencia'] ?? 25;
        $limiarTempInicio = $limiares['temporalidade_inicio'] ?? 20;
        $limiarTempFim = $limiares['temporalidade_fim'] ?? 60;
        $limiarDesempenho = $limiares['desempenho'] ?? 25;

        $adesaoPercentual = (int) ($metricas['adesao_percentual'] ?? 0);
        $aderencia = (int) ($metricas['aderencia'] ?? 0);
        $temporalidadeInicio = (int) ($metricas['temporalidade_inicio'] ?? 0);
        $temporalidadeFim = (int) ($metricas['temporalidade_fim'] ?? 0);
        $desempenho = (int) ($metricas['desempenho'] ?? 0);

        if ($adesaoPercentual <= 0) {
            return [
                'normalizado' => 'sem_resultado',
                'rotulo' => 'Sem participação',
                'tem_resultado' => false,
            ];
        }

        if ($aderencia >= 80 && $temporalidadeInicio <= 10 && $temporalidadeFim <= 30 && $desempenho >= 80) {
            return [
                'normalizado' => 'dificil',
                'rotulo' => 'Difícil',
                'tem_resultado' => true,
            ];
        }

        if ($aderencia >= 60 && $temporalidadeInicio <= 15 && $temporalidadeFim <= 45 && $desempenho >= 60) {
            return [
                'normalizado' => 'moderado',
                'rotulo' => 'Moderado',
                'tem_resultado' => true,
            ];
        }

        if (
            $aderencia >= $limiarAderencia
            && $temporalidadeInicio <= $limiarTempInicio
            && $temporalidadeFim <= $limiarTempFim
            && $desempenho >= $limiarDesempenho
        ) {
            return [
                'normalizado' => 'flexivel',
                'rotulo' => 'Flexível',
                'tem_resultado' => true,
            ];
        }

        return [
            'normalizado' => 'abaixo_criterio',
            'rotulo' => 'Abaixo dos critérios',
            'tem_resultado' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $metricas
     * @param  array<string, int>  $limiares
     * @return array<string, mixed>
     */
    public function avaliarEficacia(?string $cenario, array $metricas, array $limiares = []): array
    {
        $cenarioNormalizado = $this->normalizar($cenario);
        $perfil = $this->perfil($cenarioNormalizado);
        $cenarioResultado = $this->classificarResultado($metricas, $limiares);

        if (! $cenarioResultado['tem_resultado']) {
            $eficaz = null;
            $texto = 'Sem participação';
        } else {
            $posDesempenho = (int) ($metricas['desempenho'] ?? 0);
            $preDesempenho = (int) ($metricas['pre_desempenho'] ?? -1);
            $semGanhoDesempenho = $preDesempenho >= 0 && $posDesempenho <= $preDesempenho;

            if ($semGanhoDesempenho) {
                $eficaz = false;
                $texto = 'Não eficaz';
            } else {
                $eficaz = match ($cenarioNormalizado) {
                    'flexivel' => in_array($cenarioResultado['normalizado'], ['flexivel', 'moderado', 'dificil'], true),
                    'moderado' => in_array($cenarioResultado['normalizado'], ['moderado', 'dificil'], true),
                    'dificil' => $cenarioResultado['normalizado'] === 'dificil',
                    default => false,
                };
                $texto = $eficaz ? 'Eficaz' : 'Não eficaz';
            }
        }

        return [
            'cenario' => $this->rotulo($cenarioNormalizado),
            'cenario_normalizado' => $cenarioNormalizado,
            'cenario_resultado' => $cenarioResultado['rotulo'],
            'cenario_resultado_normalizado' => $cenarioResultado['normalizado'],
            'tem_resultado' => $cenarioResultado['tem_resultado'],
            'perfil' => $perfil,
            'eficaz' => $eficaz,
            'texto' => $texto,
        ];
    }
}
