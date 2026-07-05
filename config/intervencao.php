<?php

return [

    'app_titulo' => env('INTERVENCAO_APP_TITULO', 'Sistema de avaliação de intervenções pedagógicas'),
    'app_descricao' => env(
        'INTERVENCAO_APP_DESCRICAO',
        'Ferramenta para leitura de intervenções, definição de cenários de avaliação e análise de resultados por turma.'
    ),

    'turma_padrao' => env('INTERVENCAO_TURMA_PADRAO', '2º Ano A'),
    'intervencao_titulo_padrao' => env('INTERVENCAO_TITULO_PADRAO', 'Intervenção pedagógica'),

    'conteudos_intervencao' => [
        'flexivel' => env(
            'INTERVENCAO_CONTEUDO_FLEXIVEL',
            '<p><strong>Cenário flexível.</strong> Intervenção com foco em autonomia do estudante, '
            .'baixos limiares de adesão e desempenho, e janela temporal ampla para conclusão das atividades.</p>'
        ),
        'dificil' => env(
            'INTERVENCAO_CONTEUDO_DIFICIL',
            '<p><strong>Cenário difícil.</strong> Intervenção com alta exigência de adesão e desempenho, '
            .'com janela temporal mais restrita para conclusão das atividades.</p>'
        ),
    ],

];
