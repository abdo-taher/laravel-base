<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Application;

use Base\Foundation\CapabilityRegistry\Public\Exceptions\InvalidCapabilityDefinition;
use Base\Foundation\CapabilityRegistry\Public\ValueObjects\CapabilityVersion;

final class VersionConstraintMatcher
{
    public function matches(CapabilityVersion $version, string $constraint): bool
    {
        $constraint = trim($constraint);

        if (preg_match('/^=?((?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*))$/', $constraint, $parts) === 1) {
            return $version->compareTo(new CapabilityVersion($parts[1])) === 0;
        }

        if (preg_match('/^\^(0|[1-9]\d*)(?:\.(0|[1-9]\d*))?(?:\.(0|[1-9]\d*))?$/', $constraint, $parts) !== 1) {
            throw new InvalidCapabilityDefinition(sprintf(
                'Unsupported capability version constraint: %s',
                $constraint,
            ));
        }

        $major = (int) $parts[1];
        $minor = isset($parts[2]) ? (int) $parts[2] : 0;
        $patch = isset($parts[3]) ? (int) $parts[3] : 0;
        $lower = new CapabilityVersion(sprintf('%d.%d.%d', $major, $minor, $patch));

        if ($major > 0) {
            $upper = new CapabilityVersion(sprintf('%d.0.0', $major + 1));
        } elseif ($minor > 0 || ! isset($parts[3])) {
            $upper = new CapabilityVersion(sprintf('0.%d.0', $minor + 1));
        } else {
            $upper = new CapabilityVersion(sprintf('0.0.%d', $patch + 1));
        }

        return $version->compareTo($lower) >= 0 && $version->compareTo($upper) < 0;
    }
}
