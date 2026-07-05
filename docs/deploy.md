# Deploy e performance

## Otimização Laravel (produção)

Após `composer install --no-dev` e configurar `.env`:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=TurmaSeeder --force
```

Para reverter em desenvolvimento:

```bash
php artisan optimize:clear
```

Script auxiliar: `./scripts/deploy-optimize.sh`

## Docker

O `Dockerfile.prod` executa `migrate --force` no start. Para cache, adicione ao CMD ou entrypoint:

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Banco de dados em produção

| Ambiente | Recomendação |
|----------|----------------|
| Desenvolvimento / experimento único | SQLite (atual) |
| Múltiplos participantes simultâneos | **PostgreSQL** ou MySQL |

Variáveis para PostgreSQL (exemplo):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=intervencoes
DB_USERNAME=...
DB_PASSWORD=...
```

SQLite em volume Docker (`app_database`) exige backup periódico do arquivo.

## Cache de resultados

```env
RESULTADOS_CACHE_TTL=3600
```

Invalidação automática ao gerar dados sintéticos (`dados_gerados_at`).

## Geração em fila (opcional)

```env
QUEUE_CONNECTION=database
RESULTADOS_QUEUE_GENERATION=true
```

Requer worker: `php artisan queue:work`

## Variáveis úteis

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `RESULTADOS_CACHE_TTL` | 3600 | TTL do cache de agregações (segundos) |
| `RESULTADOS_QUEUE_GENERATION` | false | Gera PRÉ/PÓS via fila |
