#!/usr/bin/env bash
# Build the React SPA for production and print deploy instructions.
set -euo pipefail

cd "$(dirname "$0")"

echo "Building frontend for production (API: /backend/api)..."
npm run build

echo ""
echo "Build complete. Upload these to your live site document root (e.g. public_html):"
echo "  - dist/index.html"
echo "  - dist/index.php   (storefront entry; replaces any old index.php that showed admin login)"
echo "  - dist/assets/*"
echo "  - dist/vite.svg (if present)"
echo "  - public/.htaccess  →  copy to document root as .htaccess"
echo ""
echo "The .htaccess routes SPA pages through /backend/spa-shell so CMS content"
echo "is embedded in the first HTML response (no flash of bundled defaults)."
echo ""
echo "On the server, set in backend/.env (adjust path if needed):"
echo "  SPA_INDEX_PATH=/home/myterra/public_html/index.html"
echo ""
echo "If the homepage still shows admin login, remove any old public_html/index.php"
echo "that only loaded Laravel, then redeploy dist/index.php from this build."
echo ""
echo "Example (adjust paths for your server):"
echo "  rsync -av --delete dist/ user@server:/home/myterra/public_html/"
echo "  scp public/.htaccess user@server:/home/myterra/public_html/.htaccess"
echo ""
echo "Then on the server (backend):"
echo "  php artisan migrate --force"
echo "  php artisan email:seed-templates"
echo "  php artisan db:seed --class=HomepageSectionSeeder --force"
echo ""
echo "Hard-refresh the browser (Cmd+Shift+R) after deploy."
