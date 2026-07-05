# Histórico de migrations

O projeto acumulou migrations incrementais durante o desenvolvimento do experimento. Em ambiente **novo**, `php artisan migrate` aplica todas em ordem e funciona corretamente.

## Migrations legadas (não remover em produção)

| Arquivo | Observação |
|---------|------------|
| `2025_12_14_213739_create_avaliacoes_table.php` | Primeira criação de `avaliacoes` |
| `2025_12_14_220107_create_avaliacoes_table.php` | Guarda `hasTable` — no-op se já existir |
| `2025_12_15_145323_add_turma_to_intervencoes_table.php` | Vazia (placeholder histórico) |
| `2025_12_15_160000_add_turma_to_intervencoes_table.php` | Adiciona coluna `turma` string |

## Baseline recomendado (futuro)

Para um repositório “limpo”, pode-se criar uma branch com **uma única migration** consolidada e arquivar as antigas — somente após backup e em banco de desenvolvimento. Não executar `migrate:fresh` em produção sem backup do SQLite.

## Comandos

```bash
php artisan migrate          # aplicar pendentes
php artisan db:seed --class=TurmaSeeder   # turmas + alunos + FK em intervenções
php artisan migrate:fresh --seed          # apenas desenvolvimento
```
