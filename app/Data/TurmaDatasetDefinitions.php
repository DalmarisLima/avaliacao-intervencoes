<?php

namespace App\Data;

/**
 * Datasets sintéticos fixos por turma (PRÉ/PÓS por aluno).
 */
class TurmaDatasetDefinitions
{
    public static function dedupeByNome(array $alunos): array
    {
        $seen = [];
        $result = [];

        foreach ($alunos as $aluno) {
            $nome = $aluno['nome'] ?? '';
            if ($nome === '' || isset($seen[$nome])) {
                continue;
            }
            $seen[$nome] = true;
            $result[] = $aluno;
        }

        return $result;
    }

    public static function dataset1AnoA(): array
    {
        return self::dedupeByNome([
            // ── Alto desempenho: ganho ≥ 30 pts, atendem critérios moderado ──
            [
                'nome' => 'Ana Beatriz',
                'pre' => ['aderencia' => 42, 'temporalidade_inicio' => 68, 'temporalidade_fim' => 145, 'desempenho' => 38],
                'pos' => ['adesao' => 1, 'aderencia' => 82, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 38, 'desempenho' => 79],
            ],
            [
                'nome' => 'Carlos Eduardo',
                'pre' => ['aderencia' => 38, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 160, 'desempenho' => 35],
                'pos' => ['adesao' => 1, 'aderencia' => 78, 'temporalidade_inicio' => 14, 'temporalidade_fim' => 44, 'desempenho' => 74],
            ],
            [
                'nome' => 'Marina Souza',
                'pre' => ['aderencia' => 50, 'temporalidade_inicio' => 55, 'temporalidade_fim' => 120, 'desempenho' => 47],
                'pos' => ['adesao' => 1, 'aderencia' => 91, 'temporalidade_inicio' => 10, 'temporalidade_fim' => 35, 'desempenho' => 88],
            ],
            [
                'nome' => 'Rafael Torres',
                'pre' => ['aderencia' => 45, 'temporalidade_inicio' => 72, 'temporalidade_fim' => 138, 'desempenho' => 41],
                'pos' => ['adesao' => 1, 'aderencia' => 76, 'temporalidade_inicio' => 16, 'temporalidade_fim' => 50, 'desempenho' => 72],
            ],
            // ── Progressão consistente: ganho 18–23 pts, atendem critérios flexível ──
            [
                'nome' => 'Fernanda Lima',
                'pre' => ['aderencia' => 30, 'temporalidade_inicio' => 95, 'temporalidade_fim' => 178, 'desempenho' => 27],
                'pos' => ['adesao' => 1, 'aderencia' => 52, 'temporalidade_inicio' => 19, 'temporalidade_fim' => 56, 'desempenho' => 48],
            ],
            [
                'nome' => 'João Pedro',
                'pre' => ['aderencia' => 33, 'temporalidade_inicio' => 88, 'temporalidade_fim' => 162, 'desempenho' => 30],
                'pos' => ['adesao' => 1, 'aderencia' => 56, 'temporalidade_inicio' => 18, 'temporalidade_fim' => 54, 'desempenho' => 51],
            ],
            [
                'nome' => 'Isabela Martins',
                'pre' => ['aderencia' => 28, 'temporalidade_inicio' => 102, 'temporalidade_fim' => 190, 'desempenho' => 24],
                'pos' => ['adesao' => 1, 'aderencia' => 49, 'temporalidade_inicio' => 20, 'temporalidade_fim' => 58, 'desempenho' => 45],
            ],
            [
                'nome' => 'Lucas Ferreira',
                'pre' => ['aderencia' => 36, 'temporalidade_inicio' => 76, 'temporalidade_fim' => 148, 'desempenho' => 33],
                'pos' => ['adesao' => 1, 'aderencia' => 58, 'temporalidade_inicio' => 17, 'temporalidade_fim' => 52, 'desempenho' => 54],
            ],
            [
                'nome' => 'Daniela Costa',
                'pre' => ['aderencia' => 40, 'temporalidade_inicio' => 65, 'temporalidade_fim' => 130, 'desempenho' => 37],
                'pos' => ['adesao' => 1, 'aderencia' => 61, 'temporalidade_inicio' => 16, 'temporalidade_fim' => 48, 'desempenho' => 57],
            ],
            // ── Aderem — ganho pequeno, abaixo dos limiares ──────────────────
            [
                'nome' => 'Thiago Ramos',
                'pre' => ['aderencia' => 22, 'temporalidade_inicio' => 110, 'temporalidade_fim' => 205, 'desempenho' => 18],
                'pos' => ['adesao' => 1, 'aderencia' => 20, 'temporalidade_inicio' => 25, 'temporalidade_fim' => 68, 'desempenho' => 22],
            ],
            [
                'nome' => 'Patricia Alves',
                // Regressão em desempenho → intervenção não eficaz (regra primária)
                'pre' => ['aderencia' => 25, 'temporalidade_inicio' => 98, 'temporalidade_fim' => 185, 'desempenho' => 21],
                'pos' => ['adesao' => 1, 'aderencia' => 22, 'temporalidade_inicio' => 23, 'temporalidade_fim' => 64, 'desempenho' => 19],
            ],
            [
                'nome' => 'Gustavo Nunes',
                'pre' => ['aderencia' => 18, 'temporalidade_inicio' => 120, 'temporalidade_fim' => 215, 'desempenho' => 15],
                'pos' => ['adesao' => 1, 'aderencia' => 18, 'temporalidade_inicio' => 22, 'temporalidade_fim' => 62, 'desempenho' => 18],
            ],
            [
                'nome' => 'Camila Duarte',
                // Regressão em desempenho → intervenção não eficaz (regra primária)
                'pre' => ['aderencia' => 32, 'temporalidade_inicio' => 85, 'temporalidade_fim' => 158, 'desempenho' => 29],
                'pos' => ['adesao' => 1, 'aderencia' => 24, 'temporalidade_inicio' => 21, 'temporalidade_fim' => 62, 'desempenho' => 25],
            ],
            // ── Aderem — sem ganho em desempenho (estagnação / regressão) ────
            [
                'nome' => 'Felipe Barbosa',
                // Estagnação em desempenho → não eficaz
                'pre' => ['aderencia' => 20, 'temporalidade_inicio' => 115, 'temporalidade_fim' => 200, 'desempenho' => 17],
                'pos' => ['adesao' => 1, 'aderencia' => 26, 'temporalidade_inicio' => 30, 'temporalidade_fim' => 80, 'desempenho' => 17],
            ],
            [
                'nome' => 'Natalia Ribeiro',
                'pre' => ['aderencia' => 27, 'temporalidade_inicio' => 92, 'temporalidade_fim' => 172, 'desempenho' => 23],
                'pos' => ['adesao' => 1, 'aderencia' => 29, 'temporalidade_inicio' => 28, 'temporalidade_fim' => 74, 'desempenho' => 24],
            ],
            [
                'nome' => 'Rodrigo Mendes',
                // Regressão em desempenho → não eficaz
                'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 78, 'temporalidade_fim' => 150, 'desempenho' => 31],
                'pos' => ['adesao' => 1, 'aderencia' => 27, 'temporalidade_inicio' => 32, 'temporalidade_fim' => 78, 'desempenho' => 25],
            ],
            // ── Aderem — progressão consistente, aprovam moderado ───────────
            ['nome' => 'Ana Beatriz',    'pre' => ['aderencia' => 32, 'temporalidade_inicio' => 88, 'temporalidade_fim' => 160, 'desempenho' => 30], 'pos' => ['adesao' => 1, 'aderencia' => 65, 'temporalidade_inicio' => 14, 'temporalidade_fim' => 42, 'desempenho' => 62]],
            ['nome' => 'Bruno Costa',    'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 82, 'temporalidade_fim' => 155, 'desempenho' => 33], 'pos' => ['adesao' => 1, 'aderencia' => 68, 'temporalidade_inicio' => 13, 'temporalidade_fim' => 40, 'desempenho' => 65]],
            ['nome' => 'Carla Dias',     'pre' => ['aderencia' => 28, 'temporalidade_inicio' => 96, 'temporalidade_fim' => 172, 'desempenho' => 26], 'pos' => ['adesao' => 1, 'aderencia' => 62, 'temporalidade_inicio' => 15, 'temporalidade_fim' => 44, 'desempenho' => 60]],
            ['nome' => 'Daniel Rocha',   'pre' => ['aderencia' => 38, 'temporalidade_inicio' => 75, 'temporalidade_fim' => 148, 'desempenho' => 36], 'pos' => ['adesao' => 1, 'aderencia' => 71, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 38, 'desempenho' => 68]],
            ['nome' => 'Elena Santos',   'pre' => ['aderencia' => 30, 'temporalidade_inicio' => 91, 'temporalidade_fim' => 166, 'desempenho' => 28], 'pos' => ['adesao' => 1, 'aderencia' => 58, 'temporalidade_inicio' => 16, 'temporalidade_fim' => 46, 'desempenho' => 57]],
            ['nome' => 'Fábio Mendes',   'pre' => ['aderencia' => 25, 'temporalidade_inicio' => 100, 'temporalidade_fim' => 180, 'desempenho' => 23], 'pos' => ['adesao' => 1, 'aderencia' => 55, 'temporalidade_inicio' => 17, 'temporalidade_fim' => 48, 'desempenho' => 54]],
            ['nome' => 'Gabriela Lima',  'pre' => ['aderencia' => 40, 'temporalidade_inicio' => 70, 'temporalidade_fim' => 140, 'desempenho' => 38], 'pos' => ['adesao' => 1, 'aderencia' => 73, 'temporalidade_inicio' => 11, 'temporalidade_fim' => 36, 'desempenho' => 70]],
            ['nome' => 'Hugo Martins',   'pre' => ['aderencia' => 22, 'temporalidade_inicio' => 108, 'temporalidade_fim' => 192, 'desempenho' => 20], 'pos' => ['adesao' => 1, 'aderencia' => 52, 'temporalidade_inicio' => 18, 'temporalidade_fim' => 50, 'desempenho' => 51]],
            ['nome' => 'Isabela Ferreira','pre' => ['aderencia' => 33, 'temporalidade_inicio' => 84, 'temporalidade_fim' => 152, 'desempenho' => 31], 'pos' => ['adesao' => 1, 'aderencia' => 67, 'temporalidade_inicio' => 14, 'temporalidade_fim' => 43, 'desempenho' => 64]],
            ['nome' => 'Julia Alves',    'pre' => ['aderencia' => 29, 'temporalidade_inicio' => 94, 'temporalidade_fim' => 170, 'desempenho' => 27], 'pos' => ['adesao' => 1, 'aderencia' => 60, 'temporalidade_inicio' => 15, 'temporalidade_fim' => 45, 'desempenho' => 59]],
            // ── Não aderiram ─────────────────────────────────────────────────
            ['nome' => 'Klaus Ribeiro',  'pre' => ['aderencia' => 45, 'temporalidade_inicio' => 62, 'temporalidade_fim' => 128, 'desempenho' => 42], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Laura Nogueira', 'pre' => ['aderencia' => 27, 'temporalidade_inicio' => 98, 'temporalidade_fim' => 178, 'desempenho' => 25], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Marcos Pereira', 'pre' => ['aderencia' => 36, 'temporalidade_inicio' => 78, 'temporalidade_fim' => 150, 'desempenho' => 34], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Nathalia Couto', 'pre' => ['aderencia' => 31, 'temporalidade_inicio' => 88, 'temporalidade_fim' => 162, 'desempenho' => 29], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Otávio Gomes',   'pre' => ['aderencia' => 42, 'temporalidade_inicio' => 65, 'temporalidade_fim' => 132, 'desempenho' => 40], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1º ANO B — 22 alunos — grande turma, baixo engajamento, abaixo do limiar
    // Avg pré-desempenho ≈ 43 | avg pós-desempenho (aderentes) ≈ 38
    // → Sem ganho em desempenho → não eficaz em qualquer cenário
    // ─────────────────────────────────────────────────────────────────────────
    public static function dataset1AnoB(): array
    {
        return self::dedupeByNome([
            // ── Aderem — sem ganho em desempenho (regressão / estagnação) ───
            ['nome' => 'Alice Monteiro',  'pre' => ['aderencia' => 48, 'temporalidade_inicio' => 65, 'temporalidade_fim' => 130, 'desempenho' => 44], 'pos' => ['adesao' => 1, 'aderencia' => 32, 'temporalidade_inicio' => 25, 'temporalidade_fim' => 68, 'desempenho' => 38]],
            ['nome' => 'Bernard Souza',   'pre' => ['aderencia' => 40, 'temporalidade_inicio' => 74, 'temporalidade_fim' => 148, 'desempenho' => 36], 'pos' => ['adesao' => 1, 'aderencia' => 28, 'temporalidade_inicio' => 28, 'temporalidade_fim' => 72, 'desempenho' => 30]],
            ['nome' => 'Camila Ribeiro',  'pre' => ['aderencia' => 52, 'temporalidade_inicio' => 60, 'temporalidade_fim' => 122, 'desempenho' => 48], 'pos' => ['adesao' => 1, 'aderencia' => 35, 'temporalidade_inicio' => 23, 'temporalidade_fim' => 65, 'desempenho' => 42]],
            ['nome' => 'Diego Fonseca',   'pre' => ['aderencia' => 38, 'temporalidade_inicio' => 78, 'temporalidade_fim' => 155, 'desempenho' => 34], 'pos' => ['adesao' => 1, 'aderencia' => 22, 'temporalidade_inicio' => 30, 'temporalidade_fim' => 78, 'desempenho' => 28]],
            ['nome' => 'Elaine Castro',   'pre' => ['aderencia' => 55, 'temporalidade_inicio' => 58, 'temporalidade_fim' => 118, 'desempenho' => 50], 'pos' => ['adesao' => 1, 'aderencia' => 29, 'temporalidade_inicio' => 27, 'temporalidade_fim' => 70, 'desempenho' => 44]],
            ['nome' => 'Fausto Lima',     'pre' => ['aderencia' => 33, 'temporalidade_inicio' => 85, 'temporalidade_fim' => 165, 'desempenho' => 29], 'pos' => ['adesao' => 1, 'aderencia' => 18, 'temporalidade_inicio' => 32, 'temporalidade_fim' => 82, 'desempenho' => 23]],
            ['nome' => 'Giovana Ramos',   'pre' => ['aderencia' => 60, 'temporalidade_inicio' => 54, 'temporalidade_fim' => 110, 'desempenho' => 55], 'pos' => ['adesao' => 1, 'aderencia' => 34, 'temporalidade_inicio' => 24, 'temporalidade_fim' => 66, 'desempenho' => 48]],
            ['nome' => 'Henrique Alves',  'pre' => ['aderencia' => 28, 'temporalidade_inicio' => 92, 'temporalidade_fim' => 175, 'desempenho' => 25], 'pos' => ['adesao' => 1, 'aderencia' => 20, 'temporalidade_inicio' => 29, 'temporalidade_fim' => 75, 'desempenho' => 20]],
            ['nome' => 'Íris Tavares',    'pre' => ['aderencia' => 44, 'temporalidade_inicio' => 68, 'temporalidade_fim' => 138, 'desempenho' => 40], 'pos' => ['adesao' => 1, 'aderencia' => 31, 'temporalidade_inicio' => 26, 'temporalidade_fim' => 69, 'desempenho' => 35]],
            ['nome' => 'Jorge Barbosa',   'pre' => ['aderencia' => 36, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 32], 'pos' => ['adesao' => 1, 'aderencia' => 24, 'temporalidade_inicio' => 31, 'temporalidade_fim' => 80, 'desempenho' => 27]],
            ['nome' => 'Karla Duarte',    'pre' => ['aderencia' => 50, 'temporalidade_inicio' => 62, 'temporalidade_fim' => 126, 'desempenho' => 46], 'pos' => ['adesao' => 1, 'aderencia' => 33, 'temporalidade_inicio' => 22, 'temporalidade_fim' => 64, 'desempenho' => 40]],
            ['nome' => 'Leandro Pinto',   'pre' => ['aderencia' => 42, 'temporalidade_inicio' => 71, 'temporalidade_fim' => 142, 'desempenho' => 38], 'pos' => ['adesao' => 1, 'aderencia' => 26, 'temporalidade_inicio' => 28, 'temporalidade_fim' => 73, 'desempenho' => 32]],
            // ── Não aderiram ─────────────────────────────────────────────────
            ['nome' => 'Marcia Neves',    'pre' => ['aderencia' => 46, 'temporalidade_inicio' => 66, 'temporalidade_fim' => 134, 'desempenho' => 42], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Nathan Correia',  'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 82, 'temporalidade_fim' => 162, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Olívia Vaz',      'pre' => ['aderencia' => 53, 'temporalidade_inicio' => 59, 'temporalidade_fim' => 119, 'desempenho' => 49], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Pedro Macedo',    'pre' => ['aderencia' => 30, 'temporalidade_inicio' => 90, 'temporalidade_fim' => 172, 'desempenho' => 27], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Queila Freitas',  'pre' => ['aderencia' => 58, 'temporalidade_inicio' => 56, 'temporalidade_fim' => 114, 'desempenho' => 53], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Roberto Cunha',   'pre' => ['aderencia' => 39, 'temporalidade_inicio' => 76, 'temporalidade_fim' => 152, 'desempenho' => 35], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Sandra Leal',     'pre' => ['aderencia' => 47, 'temporalidade_inicio' => 64, 'temporalidade_fim' => 130, 'desempenho' => 43], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Tiago Melo',      'pre' => ['aderencia' => 32, 'temporalidade_inicio' => 87, 'temporalidade_fim' => 168, 'desempenho' => 28], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Ursula Nascimento','pre' => ['aderencia' => 56, 'temporalidade_inicio' => 57, 'temporalidade_fim' => 116, 'desempenho' => 51], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Vitor Cerqueira', 'pre' => ['aderencia' => 41, 'temporalidade_inicio' => 72, 'temporalidade_fim' => 144, 'desempenho' => 37], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Wanessa Brito',   'pre' => ['aderencia' => 37, 'temporalidade_inicio' => 79, 'temporalidade_fim' => 156, 'desempenho' => 33], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2º ANO A — 20 alunos — experimento (cenários Flexível e Difícil)
    // Flexível: 12/20 adesão, média pós-desempenho ≈ 68
    // Difícil: 18/20 adesão, média pós-desempenho ≈ 42
    // ─────────────────────────────────────────────────────────────────────────
    public static function dataset2AnoA(): array
    {
        return self::dataset2AnoAFlexivel();
    }

    public static function dataset2AnoAFlexivel(): array
    {
        return self::dedupeByNome([
            // ── Concluíram todas as atividades (7) ───────────────────────────
            ['nome' => 'Adriana Campos',   'pre' => ['aderencia' => 48, 'temporalidade_inicio' => 62, 'temporalidade_fim' => 128, 'desempenho' => 44], 'pos' => ['adesao' => 1, 'aderencia' => 92, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 42, 'desempenho' => 78]],
            ['nome' => 'Bruno Queirós',    'pre' => ['aderencia' => 52, 'temporalidade_inicio' => 58, 'temporalidade_fim' => 118, 'desempenho' => 48], 'pos' => ['adesao' => 1, 'aderencia' => 90, 'temporalidade_inicio' => 9,  'temporalidade_fim' => 38, 'desempenho' => 80]],
            ['nome' => 'Cintia Moura',     'pre' => ['aderencia' => 44, 'temporalidade_inicio' => 66, 'temporalidade_fim' => 134, 'desempenho' => 40], 'pos' => ['adesao' => 1, 'aderencia' => 88, 'temporalidade_inicio' => 7,  'temporalidade_fim' => 40, 'desempenho' => 75]],
            ['nome' => 'Danilo Borges',    'pre' => ['aderencia' => 50, 'temporalidade_inicio' => 60, 'temporalidade_fim' => 122, 'desempenho' => 46], 'pos' => ['adesao' => 1, 'aderencia' => 91, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 45, 'desempenho' => 74]],
            ['nome' => 'Esmeralda Vieira', 'pre' => ['aderencia' => 40, 'temporalidade_inicio' => 72, 'temporalidade_fim' => 144, 'desempenho' => 36], 'pos' => ['adesao' => 1, 'aderencia' => 89, 'temporalidade_inicio' => 18, 'temporalidade_fim' => 55, 'desempenho' => 72]],
            ['nome' => 'Fábio Azevedo',    'pre' => ['aderencia' => 55, 'temporalidade_inicio' => 55, 'temporalidade_fim' => 112, 'desempenho' => 51], 'pos' => ['adesao' => 1, 'aderencia' => 87, 'temporalidade_inicio' => 19, 'temporalidade_fim' => 52, 'desempenho' => 74]],
            ['nome' => 'Graziela Prado',   'pre' => ['aderencia' => 46, 'temporalidade_inicio' => 64, 'temporalidade_fim' => 130, 'desempenho' => 42], 'pos' => ['adesao' => 1, 'aderencia' => 93, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 41, 'desempenho' => 77]],
            // ── Concluíram parcialmente (3) ──────────────────────────────────
            ['nome' => 'Humberto Assis',   'pre' => ['aderencia' => 43, 'temporalidade_inicio' => 68, 'temporalidade_fim' => 138, 'desempenho' => 39], 'pos' => ['adesao' => 1, 'aderencia' => 52, 'temporalidade_inicio' => 17, 'temporalidade_fim' => 48, 'desempenho' => 65]],
            ['nome' => 'Iara Lopes',       'pre' => ['aderencia' => 58, 'temporalidade_inicio' => 54, 'temporalidade_fim' => 108, 'desempenho' => 54], 'pos' => ['adesao' => 1, 'aderencia' => 48, 'temporalidade_inicio' => 19, 'temporalidade_fim' => 56, 'desempenho' => 62]],
            ['nome' => 'Jeferson Cabral',  'pre' => ['aderencia' => 45, 'temporalidade_inicio' => 65, 'temporalidade_fim' => 132, 'desempenho' => 41], 'pos' => ['adesao' => 1, 'aderencia' => 55, 'temporalidade_inicio' => 18, 'temporalidade_fim' => 50, 'desempenho' => 64]],
            // ── Apenas videoaulas (2) ──────────────────────────────────────
            ['nome' => 'Kassia Braga',     'pre' => ['aderencia' => 38, 'temporalidade_inicio' => 74, 'temporalidade_fim' => 148, 'desempenho' => 34], 'pos' => ['adesao' => 1, 'aderencia' => 20, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 35, 'desempenho' => 50]],
            ['nome' => 'Leo Santana',      'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 1, 'aderencia' => 18, 'temporalidade_inicio' => 14, 'temporalidade_fim' => 38, 'desempenho' => 44]],
            // ── Não participaram (8) ───────────────────────────────────────
            ['nome' => 'Lucas Andrade',    'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Laura Nogueira',   'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Marcos Pereira',   'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Nathalia Couto',   'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Otávio Gomes',     'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Queila Freitas',   'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Sandra Leal',      'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Tiago Melo',       'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }

    public static function dataset2AnoADificil(): array
    {
        return self::dedupeByNome([
            ['nome' => 'Adriana Campos',   'pre' => ['aderencia' => 48, 'temporalidade_inicio' => 62, 'temporalidade_fim' => 128, 'desempenho' => 44], 'pos' => ['adesao' => 1, 'aderencia' => 92, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 24, 'desempenho' => 46]],
            ['nome' => 'Bruno Queirós',    'pre' => ['aderencia' => 52, 'temporalidade_inicio' => 58, 'temporalidade_fim' => 118, 'desempenho' => 48], 'pos' => ['adesao' => 1, 'aderencia' => 90, 'temporalidade_inicio' => 9,  'temporalidade_fim' => 26, 'desempenho' => 45]],
            ['nome' => 'Cintia Moura',     'pre' => ['aderencia' => 44, 'temporalidade_inicio' => 66, 'temporalidade_fim' => 134, 'desempenho' => 40], 'pos' => ['adesao' => 1, 'aderencia' => 88, 'temporalidade_inicio' => 7,  'temporalidade_fim' => 22, 'desempenho' => 44]],
            ['nome' => 'Danilo Borges',    'pre' => ['aderencia' => 50, 'temporalidade_inicio' => 60, 'temporalidade_fim' => 122, 'desempenho' => 46], 'pos' => ['adesao' => 1, 'aderencia' => 91, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 25, 'desempenho' => 49]],
            ['nome' => 'Esmeralda Vieira', 'pre' => ['aderencia' => 40, 'temporalidade_inicio' => 72, 'temporalidade_fim' => 144, 'desempenho' => 36], 'pos' => ['adesao' => 1, 'aderencia' => 85, 'temporalidade_inicio' => 9,  'temporalidade_fim' => 28, 'desempenho' => 43]],
            ['nome' => 'Fábio Azevedo',    'pre' => ['aderencia' => 55, 'temporalidade_inicio' => 55, 'temporalidade_fim' => 112, 'desempenho' => 51], 'pos' => ['adesao' => 1, 'aderencia' => 87, 'temporalidade_inicio' => 10, 'temporalidade_fim' => 30, 'desempenho' => 42]],
            ['nome' => 'Graziela Prado',   'pre' => ['aderencia' => 46, 'temporalidade_inicio' => 64, 'temporalidade_fim' => 130, 'desempenho' => 42], 'pos' => ['adesao' => 1, 'aderencia' => 93, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 23, 'desempenho' => 48]],
            ['nome' => 'Humberto Assis',   'pre' => ['aderencia' => 43, 'temporalidade_inicio' => 68, 'temporalidade_fim' => 138, 'desempenho' => 39], 'pos' => ['adesao' => 1, 'aderencia' => 86, 'temporalidade_inicio' => 9,  'temporalidade_fim' => 27, 'desempenho' => 41]],
            ['nome' => 'Iara Lopes',       'pre' => ['aderencia' => 58, 'temporalidade_inicio' => 54, 'temporalidade_fim' => 108, 'desempenho' => 54], 'pos' => ['adesao' => 1, 'aderencia' => 84, 'temporalidade_inicio' => 8,  'temporalidade_fim' => 29, 'desempenho' => 44]],
            ['nome' => 'Jeferson Cabral',  'pre' => ['aderencia' => 45, 'temporalidade_inicio' => 65, 'temporalidade_fim' => 132, 'desempenho' => 41], 'pos' => ['adesao' => 1, 'aderencia' => 82, 'temporalidade_inicio' => 14, 'temporalidade_fim' => 38, 'desempenho' => 40]],
            ['nome' => 'Kassia Braga',     'pre' => ['aderencia' => 38, 'temporalidade_inicio' => 74, 'temporalidade_fim' => 148, 'desempenho' => 34], 'pos' => ['adesao' => 1, 'aderencia' => 88, 'temporalidade_inicio' => 15, 'temporalidade_fim' => 40, 'desempenho' => 42]],
            ['nome' => 'Leo Santana',      'pre' => ['aderencia' => 35, 'temporalidade_inicio' => 80, 'temporalidade_fim' => 158, 'desempenho' => 31], 'pos' => ['adesao' => 1, 'aderencia' => 90, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 35, 'desempenho' => 39]],
            ['nome' => 'Lucas Andrade',    'pre' => ['aderencia' => 42, 'temporalidade_inicio' => 70, 'temporalidade_fim' => 140, 'desempenho' => 40], 'pos' => ['adesao' => 1, 'aderencia' => 87, 'temporalidade_inicio' => 13, 'temporalidade_fim' => 36, 'desempenho' => 43]],
            ['nome' => 'Laura Nogueira',   'pre' => ['aderencia' => 44, 'temporalidade_inicio' => 68, 'temporalidade_fim' => 136, 'desempenho' => 42], 'pos' => ['adesao' => 1, 'aderencia' => 89, 'temporalidade_inicio' => 11, 'temporalidade_fim' => 32, 'desempenho' => 45]],
            ['nome' => 'Marcos Pereira',   'pre' => ['aderencia' => 46, 'temporalidade_inicio' => 66, 'temporalidade_fim' => 132, 'desempenho' => 43], 'pos' => ['adesao' => 1, 'aderencia' => 81, 'temporalidade_inicio' => 16, 'temporalidade_fim' => 42, 'desempenho' => 38]],
            ['nome' => 'Nathalia Couto',   'pre' => ['aderencia' => 45, 'temporalidade_inicio' => 67, 'temporalidade_fim' => 134, 'desempenho' => 41], 'pos' => ['adesao' => 1, 'aderencia' => 81, 'temporalidade_inicio' => 17, 'temporalidade_fim' => 45, 'desempenho' => 40]],
            ['nome' => 'Otávio Gomes',     'pre' => ['aderencia' => 43, 'temporalidade_inicio' => 69, 'temporalidade_fim' => 137, 'desempenho' => 39], 'pos' => ['adesao' => 1, 'aderencia' => 72, 'temporalidade_inicio' => 12, 'temporalidade_fim' => 34, 'desempenho' => 37]],
            ['nome' => 'Queila Freitas',   'pre' => ['aderencia' => 41, 'temporalidade_inicio' => 71, 'temporalidade_fim' => 141, 'desempenho' => 38], 'pos' => ['adesao' => 1, 'aderencia' => 68, 'temporalidade_inicio' => 13, 'temporalidade_fim' => 36, 'desempenho' => 36]],
            ['nome' => 'Sandra Leal',      'pre' => ['aderencia' => 36, 'temporalidade_inicio' => 78, 'temporalidade_fim' => 152, 'desempenho' => 33], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Tiago Melo',       'pre' => ['aderencia' => 34, 'temporalidade_inicio' => 82, 'temporalidade_fim' => 156, 'desempenho' => 30], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3º ANO A — 18 alunos — inconsistente, pré-desempenho alto, sem ganho
    // Avg pré-desempenho ≈ 58 | avg pós-desempenho (aderentes) ≈ 50
    // → Sem ganho em desempenho → não eficaz independente de cenário
    // ─────────────────────────────────────────────────────────────────────────
    public static function dataset3AnoA(): array
    {
        return self::dedupeByNome([
            // ── Aderem — pré alto, pós mais baixo (regressão) ────────────────
            ['nome' => 'André Peixoto',   'pre' => ['aderencia' => 65, 'temporalidade_inicio' => 42, 'temporalidade_fim' => 95, 'desempenho' => 60], 'pos' => ['adesao' => 1, 'aderencia' => 55, 'temporalidade_inicio' => 18, 'temporalidade_fim' => 52, 'desempenho' => 52]],
            ['nome' => 'Betina Lemos',    'pre' => ['aderencia' => 70, 'temporalidade_inicio' => 38, 'temporalidade_fim' => 88, 'desempenho' => 65],  'pos' => ['adesao' => 1, 'aderencia' => 60, 'temporalidade_inicio' => 16, 'temporalidade_fim' => 48, 'desempenho' => 56]],
            ['nome' => 'Caio Rangel',     'pre' => ['aderencia' => 58, 'temporalidade_inicio' => 48, 'temporalidade_fim' => 105, 'desempenho' => 53], 'pos' => ['adesao' => 1, 'aderencia' => 44, 'temporalidade_inicio' => 22, 'temporalidade_fim' => 60, 'desempenho' => 45]],
            ['nome' => 'Denise Magno',    'pre' => ['aderencia' => 62, 'temporalidade_inicio' => 44, 'temporalidade_fim' => 98, 'desempenho' => 57],  'pos' => ['adesao' => 1, 'aderencia' => 50, 'temporalidade_inicio' => 20, 'temporalidade_fim' => 55, 'desempenho' => 48]],
            ['nome' => 'Everaldo Paiva',  'pre' => ['aderencia' => 55, 'temporalidade_inicio' => 50, 'temporalidade_fim' => 110, 'desempenho' => 50], 'pos' => ['adesao' => 1, 'aderencia' => 42, 'temporalidade_inicio' => 23, 'temporalidade_fim' => 62, 'desempenho' => 42]],
            ['nome' => 'Flávia Corrêa',   'pre' => ['aderencia' => 68, 'temporalidade_inicio' => 40, 'temporalidade_fim' => 92, 'desempenho' => 63],  'pos' => ['adesao' => 1, 'aderencia' => 58, 'temporalidade_inicio' => 17, 'temporalidade_fim' => 50, 'desempenho' => 54]],
            ['nome' => 'Gustavo Araújo',  'pre' => ['aderencia' => 52, 'temporalidade_inicio' => 52, 'temporalidade_fim' => 115, 'desempenho' => 48], 'pos' => ['adesao' => 1, 'aderencia' => 40, 'temporalidade_inicio' => 24, 'temporalidade_fim' => 64, 'desempenho' => 40]],
            ['nome' => 'Helena Bastos',   'pre' => ['aderencia' => 72, 'temporalidade_inicio' => 36, 'temporalidade_fim' => 84, 'desempenho' => 67],  'pos' => ['adesao' => 1, 'aderencia' => 62, 'temporalidade_inicio' => 15, 'temporalidade_fim' => 46, 'desempenho' => 58]],
            ['nome' => 'Ivan Guerreiro',  'pre' => ['aderencia' => 48, 'temporalidade_inicio' => 56, 'temporalidade_fim' => 120, 'desempenho' => 44], 'pos' => ['adesao' => 1, 'aderencia' => 36, 'temporalidade_inicio' => 26, 'temporalidade_fim' => 68, 'desempenho' => 36]],
            ['nome' => 'Juliana Faria',   'pre' => ['aderencia' => 60, 'temporalidade_inicio' => 46, 'temporalidade_fim' => 100, 'desempenho' => 55], 'pos' => ['adesao' => 1, 'aderencia' => 48, 'temporalidade_inicio' => 21, 'temporalidade_fim' => 58, 'desempenho' => 46]],
            // ── Não aderiram ─────────────────────────────────────────────────
            ['nome' => 'Kadu Siqueira',   'pre' => ['aderencia' => 64, 'temporalidade_inicio' => 43, 'temporalidade_fim' => 96, 'desempenho' => 59],  'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Luciana Feitosa', 'pre' => ['aderencia' => 57, 'temporalidade_inicio' => 49, 'temporalidade_fim' => 108, 'desempenho' => 52], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Marcos Coelho',   'pre' => ['aderencia' => 66, 'temporalidade_inicio' => 41, 'temporalidade_fim' => 93, 'desempenho' => 61],  'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Nadia Carmo',     'pre' => ['aderencia' => 50, 'temporalidade_inicio' => 54, 'temporalidade_fim' => 118, 'desempenho' => 46], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Oscar Vilaça',    'pre' => ['aderencia' => 74, 'temporalidade_inicio' => 34, 'temporalidade_fim' => 80, 'desempenho' => 69],  'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Paula Esteves',   'pre' => ['aderencia' => 53, 'temporalidade_inicio' => 51, 'temporalidade_fim' => 112, 'desempenho' => 49], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Quirino Belo',    'pre' => ['aderencia' => 61, 'temporalidade_inicio' => 45, 'temporalidade_fim' => 99, 'desempenho' => 56],  'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Rita Amorim',     'pre' => ['aderencia' => 47, 'temporalidade_inicio' => 57, 'temporalidade_fim' => 122, 'desempenho' => 43], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TURMA REFORÇO — 8 alunos — remediação, perfil misto
    // 5 aderentes: 3 com ganho modesto (passam flexível), 2 sem ganho
    // Avg pré-desempenho ≈ 22 | avg pós-desempenho ≈ 27 (aderentes)
    // → Eficaz no cenário flexível; falha em moderado e difícil
    // ─────────────────────────────────────────────────────────────────────────
    public static function datasetReforco(): array
    {
        return self::dedupeByNome([
            // ── Aderem — ganho pequeno, atende limiar flexível ───────────────
            ['nome' => 'Adrielle Mota',   'pre' => ['aderencia' => 18, 'temporalidade_inicio' => 115, 'temporalidade_fim' => 200, 'desempenho' => 15], 'pos' => ['adesao' => 1, 'aderencia' => 30, 'temporalidade_inicio' => 19, 'temporalidade_fim' => 55, 'desempenho' => 28]],
            ['nome' => 'Bernardo Faro',   'pre' => ['aderencia' => 20, 'temporalidade_inicio' => 108, 'temporalidade_fim' => 192, 'desempenho' => 17], 'pos' => ['adesao' => 1, 'aderencia' => 27, 'temporalidade_inicio' => 20, 'temporalidade_fim' => 58, 'desempenho' => 26]],
            ['nome' => 'Cristina Sauro',  'pre' => ['aderencia' => 16, 'temporalidade_inicio' => 120, 'temporalidade_fim' => 210, 'desempenho' => 13], 'pos' => ['adesao' => 1, 'aderencia' => 26, 'temporalidade_inicio' => 20, 'temporalidade_fim' => 59, 'desempenho' => 25]],
            // ── Aderem — sem ganho em desempenho ─────────────────────────────
            ['nome' => 'Diego Avelino',   'pre' => ['aderencia' => 22, 'temporalidade_inicio' => 102, 'temporalidade_fim' => 188, 'desempenho' => 19], 'pos' => ['adesao' => 1, 'aderencia' => 15, 'temporalidade_inicio' => 28, 'temporalidade_fim' => 72, 'desempenho' => 16]],
            ['nome' => 'Eliana Borba',    'pre' => ['aderencia' => 25, 'temporalidade_inicio' => 98, 'temporalidade_fim' => 182, 'desempenho' => 21], 'pos' => ['adesao' => 1, 'aderencia' => 18, 'temporalidade_inicio' => 26, 'temporalidade_fim' => 68, 'desempenho' => 18]],
            // ── Não aderiram ─────────────────────────────────────────────────
            ['nome' => 'Felipe Maia',     'pre' => ['aderencia' => 28, 'temporalidade_inicio' => 90, 'temporalidade_fim' => 175, 'desempenho' => 24], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Gisele Neto',     'pre' => ['aderencia' => 14, 'temporalidade_inicio' => 125, 'temporalidade_fim' => 215, 'desempenho' => 12], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
            ['nome' => 'Hugo Quaresma',   'pre' => ['aderencia' => 30, 'temporalidade_inicio' => 88, 'temporalidade_fim' => 170, 'desempenho' => 26], 'pos' => ['adesao' => 0, 'aderencia' => 0, 'temporalidade_inicio' => 0, 'temporalidade_fim' => 0, 'desempenho' => 0]],
        ]);
    }
}
