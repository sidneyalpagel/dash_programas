#!/usr/bin/env bash
# Atualiza o site no servidor Hestia. Rode como o usuário "programas":
#   ssh root@192.168.0.23
#   su - programas -c "~/web/programas.santahelena.pr.gov.br/private/dash_programas/deploy.sh"
set -euo pipefail

PHP=php8.4
cd "$(dirname "$0")"

$PHP artisan down --retry=30 || true
trap '$PHP artisan up' EXIT

git pull --ff-only
$PHP /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | grep -v '^Deprecation Notice' || true
$PHP artisan migrate --force
$PHP artisan optimize:clear >/dev/null
$PHP artisan optimize
$PHP artisan filament:optimize

echo "Atualizado para: $(git log --oneline -1)"
