#!/usr/bin/env bash
set -euo pipefail

FILES=(
docs/architecture/CANONICAL_ARCHITECTURE_STATE.md
docs/architecture/module-structure.md
docs/architecture/module-public-boundary.md
docs/architecture/base-package-extension-model.md
docs/architecture/extension-runtime-model.md
docs/architecture/module-dependency-matrix.md
docs/architecture/persistence-ownership.md
docs/architecture/module-manifest-contract.md
docs/architecture/capability-model.md
)

echo "================================"
echo "B1 ARCHITECTURE VALIDATION"
echo "================================"

for file in "${FILES[@]}"; do
    test -f "$file" || { echo "Missing $file"; exit 1; }
    ! grep -qE 'EOF|EOF—' "$file" || { echo "Corruption in $file"; exit 1; }
    echo "PASS $file"
done

git diff --check

echo "B1 architecture validation complete."
