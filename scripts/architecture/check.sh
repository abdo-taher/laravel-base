#!/usr/bin/env bash
set -euo pipefail

TMP_FILE="$(mktemp)"
trap 'rm -f "$TMP_FILE"' EXIT

./vendor/bin/deptrac analyse \
  --formatter=json \
  --report-uncovered \
  > "$TMP_FILE"

php scripts/architecture/check-deptrac-uncovered.php \
  < "$TMP_FILE"
