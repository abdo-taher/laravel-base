#!/usr/bin/env bash
set -euo pipefail

if ! command -v gitleaks >/dev/null 2>&1; then
    echo "gitleaks is required but was not found in PATH." >&2
    exit 1
fi

echo "Scanning Git history for secrets..."

gitleaks detect \
    --config .gitleaks.toml \
    --source . \
    --no-banner \
    --redact

echo "Scanning current working tree for secrets..."

gitleaks detect \
    --config .gitleaks.toml \
    --source . \
    --no-git \
    --no-banner \
    --redact

echo "Secret scanning passed."
