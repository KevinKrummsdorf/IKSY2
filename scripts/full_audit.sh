#!/usr/bin/env bash
set -euo pipefail
mkdir -p build

# 1) Abhängigkeiten & Umgebung
composer install --prefer-dist --no-interaction
docker compose up -d --quiet-pull db redis || true          # falls vorhanden
php -r "copy('.env.example','.env');" 2>/dev/null || true
php artisan key:generate 2>/dev/null || true

# 2) SECURITY
composer audit --no-dev --format=json   > build/composer_audit.json
vendor/bin/psalm --taint-analysis --output-format=json \
                                  > build/psalm_taint.json
npx --yes @zaproxy/zap-cli quick-scan --self-contained \
        --spider-time 0 http://localhost        || true
grep -R --line-number -E "\$_(GET|POST|REQUEST|COOKIE)\['[^']+'\]" src \
                                  > build/raw_superglobals.txt

# 3) XSS / SQL-Injection Tests
vendor/bin/phpunit --testsuite security --coverage-text \
                                  > build/phpunit_security.txt

# 4) PERFORMANCE / EFFIZIENZ
vendor/bin/phpbench run --report=aggregate            --output=build/phpbench.xml
vendor/bin/phpmetrics --report-html=build/metrics     >/dev/null
ab -n 500 -c 25 http://localhost/                     > build/ab.txt

# 5) CODE-QUALITÄT
vendor/bin/phpstan analyse --error-format raw         > build/phpstan.txt
vendor/bin/phpcs --standard=PSR12 --report=full src/  > build/phpcs.txt
vendor/bin/phpcpd src/                                > build/duplication.txt
vendor/bin/deptrac --formatter=graphviz \
                   --output=build/architecture.dot    || true

# 6) DEPENDENCY-HYGIENE
composer outdated --direct               > build/outdated.txt
composer licenses --format=json          > build/licenses.json || true

# 7) MUTATION TESTING
vendor/bin/infection --threads=4 --only-covered \
                     --log-verbosity=all \
                     --text=build/infection.txt        || true

# 8) ZUSAMMENFASSUNG
php -r '
$out = "## Projekt-Audit (".date("Y-m-d").")\n\n";
foreach (glob("build/*.txt") as $f) {
  $out .= "### ".basename($f)."\n```\n".file_get_contents($f)."```\n\n";
}
file_put_contents("AUDIT_SUMMARY.md", $out);
'

