# Regras de negócio — Intervenções App

## Fluxos de avaliação

### Automático (padrão do experimento)

1. Usuário cria intervenção e escolhe a **turma**.
2. Define **cenário** e **limiares** (`aderência`, `temporalidade_início`, `temporalidade_fim`, `desempenho`).
3. O sistema gera avaliações **PRÉ** e **PÓS** para cada aluno do dataset da turma (dados fixos em `database/data/turmas/*.json`).
4. A intervenção recebe `dados_gerados_at` e **não permite** nova geração nem avaliação manual.

### Manual (alternativo)

Disponível apenas **antes** da geração automática. Registra uma avaliação `pos` agregada (`aluno_nome`: "Avaliação manual") para testes ou casos especiais.

## Cenários

| Valor interno | Rótulo    | Uso |
|---------------|-----------|-----|
| `flexivel`    | Flexível  | Limiares mais baixos; aceita resultado flexível, moderado ou difícil |
| `moderado`    | Moderado  | Limiares intermediários; aceita moderado ou difícil |
| `dificil`     | Difícil   | Limiares altos; só eficaz se atingir nível difícil |

Sinônimos aceitos no formulário: *leve* → flexível; *rígido/modelado* → moderado; *personalizado* → difícil.

## Classificação do resultado (métricas agregadas)

Ordem de avaliação (do mais rigoroso ao configurável):

1. **Sem resultado** — adesão percentual ≤ 0.
2. **Difícil** — aderência ≥ 80, temp. início ≤ 10, temp. fim ≤ 30, desempenho ≥ 80.
3. **Moderado** — aderência ≥ 60, temp. início ≤ 15, temp. fim ≤ 45, desempenho ≥ 60.
4. **Flexível** — atende aos limiares configurados na intervenção.
5. **Abaixo dos critérios** — caso contrário.

## Eficácia da intervenção

Documentação completa para a dissertação e explicação em linguagem simples: [`metodologia-avaliacao-eficacia.md`](metodologia-avaliacao-eficacia.md) (download em `/docs/metodologia-eficacia` na aplicação).

**Regra primária:** se o desempenho pós ≤ desempenho pré, a intervenção é **não eficaz**, independentemente dos outros indicadores.

Caso haja ganho em desempenho, a eficácia depende do cenário escolhido:

| Cenário configurado | Considerada eficaz quando o resultado atinge |
|---------------------|-----------------------------------------------|
| Flexível            | Flexível, Moderado ou Difícil                 |
| Moderado            | Moderado ou Difícil                           |
| Difícil             | Apenas Difícil                                |

Textos exibidos: `Eficaz`, `Não eficaz`, `Sem relevância` (sem adesão).

## Turmas e datasets

Cada turma possui um perfil de alunos sintéticos (adesão, métricas pré/pós fixas). O cenário e os limiares **não alteram** os valores gravados — apenas a **interpretação** nos resultados.

| Turma          | Arquivo JSON        | Perfil resumido                          |
|----------------|---------------------|------------------------------------------|
| 1º Ano A       | `1-ano-a.json`      | Progressão mista; moderado costuma passar |
| 1º Ano B       | `1-ano-b.json`      | Baixo ganho; tende a não ser eficaz       |
| 2º Ano A       | `2-ano-a.json`      | Alta performance; passa cenário difícil |
| 3º Ano A       | `3-ano-a.json`      | Regressão de desempenho                  |
| Turma Reforço  | `reforco.json`      | Ganho modesto; flexível                  |

Nomes de alunos duplicados no dataset fonte são removidos (mantém a primeira ocorrência).

## Autorização

- Cada intervenção pertence a um `user_id`.
- Apenas o dono pode visualizar, configurar cenário ou avaliar.

## Integridade de dados

- Unique: `(intervencao_id, aluno_numero, tipo)` em `avaliacoes`.
- `dados_gerados_at` impede regeneração acidental dos pares PRÉ/PÓS.
