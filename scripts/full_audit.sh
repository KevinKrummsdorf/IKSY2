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
if [ -x vendor/bin/psalm ]; then
  vendor/bin/psalm --taint-analysis --output-format=json \
                                  > build/psalm_taint.json
else
  echo '{}' > build/psalm_taint.json
  echo 'psalm not installed, skipping' >&2
fi
npx --yes @zaproxy/zap-cli quick-scan --self-contained \
        --spider-time 0 http://localhost        || true
grep -R --line-number -E "\$_(GET|POST|REQUEST|COOKIE)\['[^']+'\]" src \
                                  > build/raw_superglobals.txt || true

# 3) XSS / SQL-Injection Tests
if [ -x vendor/bin/phpunit ]; then
  vendor/bin/phpunit --testsuite security --coverage-text \
                                  > build/phpunit_security.txt
else
  echo 'phpunit not installed' > build/phpunit_security.txt
fi

# 4) PERFORMANCE / EFFIZIENZ
if [ -x vendor/bin/phpbench ]; then
  vendor/bin/phpbench run --report=aggregate --output=build/phpbench.xml
else
  echo '<phpbench/>' > build/phpbench.xml
fi
if [ -x vendor/bin/phpmetrics ]; then
  vendor/bin/phpmetrics --report-html=build/metrics     >/dev/null
else
  echo 'phpmetrics not installed' > build/metrics.txt
fi
ab -n 500 -c 25 http://localhost/                     > build/ab.txt || true

# 5) CODE-QUALITÄT
if [ -x vendor/bin/phpstan ]; then
  vendor/bin/phpstan analyse --error-format raw         > build/phpstan.txt
else
  echo 'phpstan not installed' > build/phpstan.txt
fi
if [ -x vendor/bin/phpcs ]; then
  vendor/bin/phpcs --standard=PSR12 --report=full src/  > build/phpcs.txt
else
  echo 'phpcs not installed' > build/phpcs.txt
fi
if [ -x vendor/bin/phpcpd ]; then
  vendor/bin/phpcpd src/                                > build/duplication.txt
else
  echo 'phpcpd not installed' > build/duplication.txt
fi
if [ -x vendor/bin/deptrac ]; then
  vendor/bin/deptrac --formatter=graphviz \
                   --output=build/architecture.dot    || true
fi

# 6) DEPENDENCY-HYGIENE
composer outdated --direct               > build/outdated.txt
composer licenses --format=json          > build/licenses.json || true

# 7) MUTATION TESTING
if [ -x vendor/bin/infection ]; then
  vendor/bin/infection --threads=4 --only-covered \
                     --log-verbosity=all \
                     --text=build/infection.txt        || true
else
  echo 'infection not installed' > build/infection.txt
fi

# 8) ZUSAMMENFASSUNG
php -r '
$out = "## Projekt-Audit (".date("Y-m-d").")\n\n";
foreach (glob("build/*.txt") as $f) {
  $out .= "### ".basename($f)."\n```\n".file_get_contents($f)."```\n\n";
}
file_put_contents("AUDIT_SUMMARY.md", $out);
'
