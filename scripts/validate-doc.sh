#!/usr/bin/env bash
set -euo pipefail

for file in "$@"; do
    if [ ! -f "$file" ]; then
        echo "ERROR: missing file: $file"
        exit 1
    fi

    echo "Checking: $file"

    if grep -qE 'EOF|EOF—' "$file"; then
        echo "ERROR: corruption marker found in $file"
        exit 1
    fi
done

git diff --check

echo
echo "Documentation validation passed."
echo
git status --short
