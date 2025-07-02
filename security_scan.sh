#!/bin/bash

# Simple security scanning script for PHP code.
# Scans for potential XSS and SQL injection vulnerabilities and logs results.

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
OUTPUT_FILE="$ROOT_DIR/security_report.txt"

# Clear previous report
> "$OUTPUT_FILE"

echo "Sicherheitsreport $(date)" >> "$OUTPUT_FILE"
echo "==============================" >> "$OUTPUT_FILE"
echo >> "$OUTPUT_FILE"

# Function to run a grep search and append results with a heading
scan() {
    local heading="$1"
    local pattern="$2"
    echo "${heading}" >> "$OUTPUT_FILE"
    # Exclude vendor and .git directories
    grep -nR --exclude-dir=vendor --exclude-dir=.git --include='*.php' -E "$pattern" "$ROOT_DIR" 2>/dev/null >> "$OUTPUT_FILE"
    echo >> "$OUTPUT_FILE"
}

# Check for possible XSS: output of superglobals without sanitization
scan "Verdacht auf XSS (unsanitized output of superglobals):" "(echo|print).*\$_(GET|POST|REQUEST|COOKIE)"

# Check for possible SQL injection: user input inside SQL queries
scan "Verdacht auf SQL Injection (user input im SQL-Statement):" "(SELECT|INSERT|UPDATE|DELETE).*\$_(GET|POST|REQUEST|COOKIE)"

# Check for dangerous PHP functions
scan "Verwendung potentiell gefaehrlicher Funktionen:" "\b(eval|system|shell_exec|passthru|exec)\b"

echo "Scan abgeschlossen: $(date)" >> "$OUTPUT_FILE"
echo "Report gespeichert in $OUTPUT_FILE" >> "$OUTPUT_FILE"

echo "Sicherheitsanalyse abgeschlossen. Bericht in $OUTPUT_FILE erstellt." 
