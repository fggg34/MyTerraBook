#!/usr/bin/env bash
# Start MyTerraBook locally (frontend :5174, backend :8080)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

export BACKEND_PORT="${BACKEND_PORT:-8080}"
export FRONTEND_PORT="${FRONTEND_PORT:-5174}"

echo "MyTerraBook dev, backend http://127.0.0.1:${BACKEND_PORT}  frontend http://127.0.0.1:${FRONTEND_PORT}"
echo "Admin login: http://127.0.0.1:${BACKEND_PORT}/admin/login  (admin@terrabook.test / password)"
echo ""

cd "$ROOT/backend"
php artisan config:clear --quiet 2>/dev/null || true
php artisan optimize:clear --quiet 2>/dev/null || true

if ! php artisan migrate --force --quiet 2>/dev/null; then
  echo "Running fresh migrations + seed..."
  php artisan migrate:fresh --seed --force
fi

php artisan serve --host=127.0.0.1 --port="$BACKEND_PORT" &
BACKEND_PID=$!

cd "$ROOT/frontend"
npm run dev -- --port "$FRONTEND_PORT" --host 127.0.0.1 &
FRONTEND_PID=$!

trap 'kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; exit' INT TERM

wait
