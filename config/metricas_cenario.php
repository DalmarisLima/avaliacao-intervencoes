<?php

/**
 * Textos de ajuda — limiares e métricas na configuração de cenário (Etapa 2).
 */
return [
    'intro' => 'Ao definir o cenário, você estabelece os critérios mínimos que a turma deve atingir na avaliação pós-intervenção. O sistema compara a média da turma com esses limiares para interpretar se a intervenção foi eficaz.',

    'cenarios' => 'Flexível e Difícil trazem valores sugeridos. Você pode ajustá-los nos controles abaixo conforme a exigência que deseja para esta turma.',

    'temporalidade_destaque' => [
        'titulo' => 'Como entender a temporalidade',
        'texto' => 'A temporalidade mede o ritmo com que os alunos realizam a intervenção — não a duração da aula, e sim o tempo registrado em duas etapas distintas. Nos resultados, quanto menor o tempo médio da turma, melhor — desde que dentro do limite que você definir aqui.',
        'itens' => [
            'Tempo para iniciar: prazo máximo, em minutos, para o aluno começar a atividade depois que ela fica disponível. Ex.: 20 min significa que a turma deve, em média, iniciar em até 20 minutos.',
            'Tempo para finalizar: prazo máximo, em minutos, para concluir a atividade após tê-la iniciado. Ex.: 60 min significa que a conclusão deve ocorrer em até 1 hora.',
            'Quanto menor o valor no controle, mais exigente é o critério. O tempo para finalizar deve ser maior que o tempo para iniciar.',
        ],
    ],

    'metricas' => [
        'aderencia' => [
            'label' => 'Aderência mínima',
            'hint' => 'Entre os alunos que participaram, percentual mínimo da proposta pedagógica que deve ter sido executada (tarefas realizadas). Quanto maior o limiar, mais rigoroso é o critério de cumprimento da intervenção.',
        ],
        'temporalidade_inicio' => [
            'label' => 'Tempo para iniciar (máx.)',
            'hint' => 'Prazo máximo, em minutos, para a turma começar a atividade após ela ficar disponível. Valores menores exigem que os alunos iniciem mais rápido.',
        ],
        'temporalidade_fim' => [
            'label' => 'Tempo para finalizar (máx.)',
            'hint' => 'Prazo máximo, em minutos, para concluir a atividade após o início. Deve ser maior que o tempo para iniciar. Valores menores exigem conclusão mais rápida.',
        ],
        'desempenho' => [
            'label' => 'Desempenho mínimo',
            'hint' => 'Percentual mínimo de aprendizagem na avaliação pós-intervenção. Indicador central: sem ganho em relação ao pré-teste, a intervenção não é considerada eficaz, mesmo com bons valores nas demais métricas.',
        ],
    ],
];
