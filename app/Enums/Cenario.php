<?php

namespace App\Enums;

enum Cenario: string
{
    case Flexivel = 'flexivel';
    case Moderado = 'moderado';
    case Dificil = 'dificil';

    public function rotulo(): string
    {
        return match ($this) {
            self::Flexivel => 'Flexível',
            self::Moderado => 'Moderado',
            self::Dificil => 'Difícil',
        };
    }

    /** Rótulo exibido ao participante no experimento (moderado = rígido). */
    public function rotuloExperimento(): string
    {
        return match ($this) {
            self::Flexivel => 'Flexível',
            self::Moderado => 'Rígido',
            self::Dificil => 'Difícil',
        };
    }

    /**
     * Limiares padrão sugeridos para o perfil do cenário.
     *
     * @return array{adesao: string, aderencia: int, temporalidade_inicio: int, temporalidade_fim: int, desempenho: int}
     */
    public function limiaresPadrao(): array
    {
        return match ($this) {
            self::Flexivel => [
                'adesao' => 'sim',
                'aderencia' => 25,
                'temporalidade_inicio' => 20,
                'temporalidade_fim' => 60,
                'desempenho' => 25,
            ],
            self::Moderado => [
                'adesao' => 'sim',
                'aderencia' => 60,
                'temporalidade_inicio' => 15,
                'temporalidade_fim' => 45,
                'desempenho' => 60,
            ],
            self::Dificil => [
                'adesao' => 'sim',
                'aderencia' => 80,
                'temporalidade_inicio' => 10,
                'temporalidade_fim' => 30,
                'desempenho' => 80,
            ],
        };
    }

    /** Rótulo ordinal usado na tabela de resultados do experimento. */
    public function rotuloOrdemExperimento(): ?string
    {
        return match ($this) {
            self::Flexivel => 'Cenário 1',
            self::Dificil => 'Cenário 2',
            default => null,
        };
    }

    public static function tituloTabela(?string $valor, ?string $fallback = null): string
    {
        if ($valor === null || trim($valor) === '') {
            return $fallback ?? 'Intervenção';
        }

        $normalizado = mb_strtolower(trim($valor));
        $normalizado = str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
            $normalizado
        );

        $cenario = match ($normalizado) {
            'leve', 'flexivel', 'flexível' => self::Flexivel,
            'dificil', 'difícil', 'personalizado' => self::Dificil,
            default => null,
        };

        if ($cenario !== null) {
            return $cenario->rotuloOrdemExperimento() ?? ($fallback ?? 'Intervenção');
        }

        return $fallback ?? 'Intervenção';
    }

    public static function fromInput(string $valor): self
    {
        $valor = trim(mb_strtolower($valor));
        $valor = str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
            $valor
        );

        $normalizado = match ($valor) {
            'leve', 'flexivel' => self::Flexivel->value,
            'rigido', 'rígido', 'moderado', 'modelado', 'modelo' => self::Moderado->value,
            'dificil', 'difícil', 'personalizado' => self::Dificil->value,
            default => $valor !== '' ? $valor : self::Flexivel->value,
        };

        return self::tryFrom($normalizado) ?? self::Flexivel;
    }

    /**
     * @return array<int, string>
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Cenários disponíveis na tela de configuração (sem moderado). */
    public static function paraConfiguracao(): array
    {
        return [self::Flexivel, self::Dificil];
    }
}
