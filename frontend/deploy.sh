#!/usr/bin/env bash
# Build the React SPA for production and print deploy instructions.
set -euo pipefail

cd "$(dirname "$0")"

echo "Building frontend for production (API: /backend/api)..."
npm run build

echo ""
echo "Build complete. Upload these to your live site document root (e.g. public_html):"
echo "  - dist/index.html"
echo "  - dist/assets/*"
echo "  - dist/vite.svg (if present)"
echo "  - public/.htaccess  →  copy to document root as .htaccess"
echo ""
echo "Example (adjust paths for your server):"
echo "  rsync -av --delete dist/ user@server:/home/myterra/public_html/"
echo "  scp public/.htaccess user@server:/home/myterra/public_html/.htaccess"
echo ""
echo "Then on the server (backend):"
echo "  php artisan migrate --force"
echo "  php artisan db:seed --class=HomepageSectionSeeder --force"
echo ""
echo "Hard-refresh the browser (Cmd+Shift+R) after deploy."
