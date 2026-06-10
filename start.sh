#!/bin/bash
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

PHP_BIN="/opt/homebrew/bin/php"
if [ ! -x "$PHP_BIN" ]; then
  PHP_BIN="$(command -v php || true)"
fi

if [ -z "$PHP_BIN" ]; then
  echo "PHP not found. Install with: brew install php"
  exit 1
fi

echo "Starting PostgreSQL (if needed)..."
brew services start postgresql@17 2>/dev/null || true

echo ""
echo "TRY KUET site is starting..."
echo ""
echo "  Site:  http://localhost:8000/index.html"
echo "  Join:  http://localhost:8000/join.html"
echo "  Admin: http://localhost:8000/admin/login.php"
echo ""
echo "Press Ctrl+C to stop the server."
echo ""

exec "$PHP_BIN" -S localhost:8000
