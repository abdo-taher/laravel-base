#!/usr/bin/env bash
set -euo pipefail

readonly REPOSITORY_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPOSITORY_ROOT"

readonly CANONICAL_ARCHITECTURE="docs/architecture/CANONICAL_ARCHITECTURE_STATE.md"
readonly B1_ROADMAP="docs/roadmap/B1-module-structure-contract.md"

readonly -a REQUIRED_ARCHITECTURE_DOCUMENTS=(
    "$CANONICAL_ARCHITECTURE"
    "docs/architecture/module-structure.md"
    "docs/architecture/module-public-boundary.md"
    "docs/architecture/base-package-extension-model.md"
    "docs/architecture/extension-runtime-model.md"
    "docs/architecture/module-dependency-matrix.md"
    "docs/architecture/persistence-ownership.md"
    "docs/architecture/module-manifest-contract.md"
    "docs/architecture/capability-model.md"
)

fail() {
    printf 'FAIL: %s\n' "$1" >&2
    exit 1
}

require_literal() {
    local file="$1"
    local text="$2"
    local description="$3"

    grep -Fq -- "$text" "$file" || fail "$description: $file"
}

printf '%s\n' 'B1 architecture documentation governance validation'

for document in "${REQUIRED_ARCHITECTURE_DOCUMENTS[@]}"; do
    [[ -f "$document" ]] || fail "required architecture document is missing: $document"

    if grep -nE '(^|[[:space:]])EOF(—|[[:space:]]*$)' "$document" >/dev/null; then
        fail "EOF corruption marker found: $document"
    fi

    printf 'PASS: %s\n' "$document"
done

[[ -f "$B1_ROADMAP" ]] || fail "B1 roadmap is missing: $B1_ROADMAP"

require_literal "$CANONICAL_ARCHITECTURE" 'ACCEPTED ARCHITECTURE SOURCE OF TRUTH' \
    'canonical architecture acceptance marker is missing'
require_literal "$CANONICAL_ARCHITECTURE" 'packages/base/' \
    'canonical Base package ownership path is missing'
require_literal "$CANONICAL_ARCHITECTURE" 'extensions/' \
    'canonical extension ownership path is missing'
require_literal "$CANONICAL_ARCHITECTURE" 'modules/' \
    'canonical business module ownership path is missing'
require_literal "$CANONICAL_ARCHITECTURE" '## Superseded Layout' \
    'canonical superseded-layout declaration is missing'

while IFS= read -r reference; do
    [[ -f "$reference" ]] || fail "roadmap references a missing architecture document: $reference"
done < <(grep -hoE 'docs/architecture/[[:alnum:]_./-]+\.md' docs/roadmap/*.md | sort -u)

require_literal "$B1_ROADMAP" 'docs/architecture/module-structure.md' \
    'B1 roadmap module-structure reference is missing'
require_literal "$B1_ROADMAP" 'docs/architecture/module-public-boundary.md' \
    'B1 roadmap public-boundary reference is missing'
require_literal "$B1_ROADMAP" 'docs/architecture/module-dependency-matrix.md' \
    'B1 roadmap dependency-matrix reference is missing'
require_literal "$B1_ROADMAP" 'docs/architecture/base-package-extension-model.md' \
    'B1 roadmap package-extension reference is missing'
require_literal "$B1_ROADMAP" 'partially supersedes the original single `Modules/` physical-root assumption' \
    'B1 roadmap does not reconcile the superseded single-root layout'

readonly -a CONFLICTING_WORDING=(
    'Project-specific customization belongs in packages/base/'
    'Business modules belong in packages/base/'
    'Base packages may depend on project modules'
    'Base packages may depend on project extensions'
    'The single Modules/ layout is the final repository model'
)

for wording in "${CONFLICTING_WORDING[@]}"; do
    if grep -Fq -- "$wording" "${REQUIRED_ARCHITECTURE_DOCUMENTS[@]}" "$B1_ROADMAP"; then
        fail "conflicting architecture wording remains: $wording"
    fi
done

printf '%s\n' 'Architecture documentation governance validation passed.'
