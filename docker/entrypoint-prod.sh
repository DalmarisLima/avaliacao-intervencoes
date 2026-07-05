#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

mkdir -p storage/framework/{views,cache,sessions} storage/logs database
chown -R www-data:www-data storage bootstrap/cache database

if [ -n "${APP_URL:-}" ]; then
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
  if [[ "${APP_URL}" == https://* ]]; then
    if grep -q '^SESSION_SECURE_COOKIE=' .env; then
      sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env
    else
      echo 'SESSION_SECURE_COOKIE=true' >> .env
    fi
  fi
fi

APP_KEY_FILE="/var/www/html/database/.app_key"

if [ -n "${APP_KEY:-}" ]; then
  sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
elif [ -f "$APP_KEY_FILE" ]; then
  sed -i "s|^APP_KEY=.*|APP_KEY=$(cat "$APP_KEY_FILE")|" .env
elif grep -q '^APP_KEY=base64:' .env; then
  grep '^APP_KEY=' .env | cut -d= -f2- > "$APP_KEY_FILE"
  chown www-data:www-data "$APP_KEY_FILE"
  chmod 600 "$APP_KEY_FILE"
else
  php artisan key:generate --force
  grep '^APP_KEY=' .env | cut -d= -f2- > "$APP_KEY_FILE"
  chown www-data:www-data "$APP_KEY_FILE"
  chmod 600 "$APP_KEY_FILE"
fi

if [ -n "${EXPERIMENTO_ADMIN_EMAILS:-}" ]; then
  if grep -q '^EXPERIMENTO_ADMIN_EMAILS=' .env; then
    sed -i "s|^EXPERIMENTO_ADMIN_EMAILS=.*|EXPERIMENTO_ADMIN_EMAILS=${EXPERIMENTO_ADMIN_EMAILS}|" .env
  else
    echo "EXPERIMENTO_ADMIN_EMAILS=${EXPERIMENTO_ADMIN_EMAILS}" >> .env
  fi
fi

DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
export DB_DATABASE="$DB_FILE"

if [ ! -f "$DB_FILE" ]; then
  echo "[entrypoint] Criando banco SQLite em ${DB_FILE} (primeira execução ou volume novo)."
  touch "$DB_FILE"
  chown www-data:www-data "$DB_FILE"
fi

php artisan migrate --force

if php artisan list --raw 2>/dev/null | grep -q '^experimento:ensure-schema'; then
  php artisan experimento:ensure-schema || echo "[entrypoint] AVISO: ensure-schema falhou; será tentado no primeiro acesso."
else
  echo "[entrypoint] Comando experimento:ensure-schema ausente nesta imagem; pulando."
fi

php artisan db:seed --class=TurmaSeeder --force
php artisan db:seed --class=ExperimentoSeeder --force
php artisan config:clear
php artisan route:cache
php artisan view:cache

exec apache2-foreground
