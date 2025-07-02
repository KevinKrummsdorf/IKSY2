#!/usr/bin/env bash
set -euo pipefail
mkdir -p build tools

# ------------------------------------------------------------
# 0) Helfer – Dev-Pakete nachziehen (-W = full-update)
# ------------------------------------------------------------
require_dev() {
  local pkg="$1"
  if ! grep -q "\"${pkg%%:*}\"" composer.json 2>/dev/null; then
    echo "  » $pkg nachziehen …"
    composer require --dev -W --no-interaction --no-progress "$pkg"
  fi
}

# ------------------------------------------------------------
# 1) Abhängigkeiten & QA-Toolchain
# ------------------------------------------------------------
composer install --prefer-dist --no-interaction

echo "🔧 QA-Toolchain installieren (falls nötig)…"
require_dev "phpunit/phpunit"
require_dev "phpstan/phpstan"
require_dev "infection/infection"
require_dev "phpmetrics/phpmetrics:^3@dev"
require_dev "squizlabs/php_codesniffer"

# Psalm als PHAR (unabhängig von Composer)
if [ ! -x tools/psalm.phar ]; then
  curl -sSL \
    https://github.com/vimeo/psalm/releases/latest/download/psalm.phar \
    -o tools/psalm.phar && chmod +x tools/psalm.phar
fi

# ------------------------------------------------------------
# 2) Optionale Container (DB/Redis)
# ------------------------------------------------------------
if command -v docker >/dev/null 2>&1 && [ -f docker-compose.yml ]; then
  docker compose up -d --quiet-pull db redis || true
fi

php -r "file_exists('.env') ?: @copy('.env.example','.env');" || true
php artisan key:generate 2>/dev/null || true

# ------------------------------------------------------------
# 3) SECURITY-CHECKS
# ------------------------------------------------------------
composer audit --no-dev --format=json > build/composer_audit.json
php tools/psalm.phar --taint-analysis --no-cache --output-format=json \
                     > build/psalm_taint.json || true

if command -v npx >/dev/null 2>&1; then
  npx --yes @zaproxy/zap-cli quick-scan --self-contained \
      --spider-time 0 http://localhost \
      > build/zap.txt 2>&1 || true
else
  echo 'Node.js (npm/npx) fehlt – ZAP übersprungen' > build/zap.txt
fi

grep -R --line-number -E "\$_(GET|POST|REQUEST|COOKIE)\['[^']+'\]" src \
     > build/raw_superglobals.txt || true

# ------------------------------------------------------------
# 4) UNIT-Tests (XSS / SQLi …)
# ------------------------------------------------------------
if [ -x vendor/bin/phpunit ] && ls phpunit*.xml* phpunit*.yml* 1>/dev/null 2>&1
then
  vendor/bin/phpunit --coverage-text > build/phpunit_security.txt || true
else
  echo 'Kein PHPUnit-Config-File gefunden – Test-Suite übersprungen' \
       > build/phpunit_security.txt
fi

# ------------------------------------------------------------
# 5) PERFORMANCE / EFFIZIENZ
# ------------------------------------------------------------
vendor/bin/phpbench run --report=aggregate --output=build/phpbench.xml || true
vendor/bin/phpmetrics --report-html=build/metrics ./src              || true
command -v ab >/dev/null 2>&1 && \
  ab -n 500 -c 25 http://localhost/ > build/ab.txt || true

# ------------------------------------------------------------
# 6) CODE-QUALITÄT
# ------------------------------------------------------------
PHPSTAN_PATH="src"; [ -d "$PHPSTAN_PATH" ] || PHPSTAN_PATH="."
vendor/bin/phpstan analyse "$PHPSTAN_PATH" --no-progress --error-format raw \
                         > build/phpstan.txt  || true
vendor/bin/phpcs --standard=PSR12 --report=full src/ \
                 > build/phpcs.txt            || true
vendor/bin/phpcpd src/ > build/duplication.txt || true
[ -x vendor/bin/deptrac ] && \
  vendor/bin/deptrac --formatter=graphviz \
                     --output=build/architecture.dot || true

# ------------------------------------------------------------
# 7) DEPENDENCY-HYGIENE
# ------------------------------------------------------------
composer outdated --direct      > build/outdated.txt
composer licenses --format=json > build/licenses.json || true

# ------------------------------------------------------------
# 8) MUTATION-Tests
# ------------------------------------------------------------
if [ ! -f infection.json ]; then
cat > infection.json <<'JSON'
{
  "source": { "directories": ["src"] },
  "logs":   { "text": "build/infection-log.txt" }
}
JSON
fi
vendor/bin/infection --threads=4 --only-covered --no-interaction \
                     > build/infection.txt || true

# ------------------------------------------------------------
# 9) SUMMARY
# ------------------------------------------------------------
php -r '
$out = "## Projekt-Audit (".date("Y-m-d").")\n\n";
foreach (glob("build/*.txt") as $f) {
  $out .= "### ".basename($f)."\n```\n".file_get_contents($f)."```\n\n";
}
file_put_contents("AUDIT_SUMMARY.md", $out);
'
echo "✅ Audit abgeschlossen – Berichte liegen in ./build/ und AUDIT_SUMMARY.md"
