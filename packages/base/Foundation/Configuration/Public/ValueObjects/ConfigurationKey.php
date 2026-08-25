<?php

declare(strict_types=1);

namespace Base\Foundation\Configuration\Public\ValueObjects;

use InvalidArgumentException;

/**
 * A typed configuration key.
 *
 * Captures the key path, the expected PHP type, and whether the key is
 * required. An optional definition-level default may be supplied for
 * optional keys.
 *
 * No framework dependencies. Keys are value objects — create them as
 * constants or static factory methods on the owning package's
 * configuration class.
 *
 * Supported type tags: 'string', 'int', 'float', 'bool', 'array'
 */
final readonly class ConfigurationKey
{
    public const string TYPE_STRING = 'string';

    public const string TYPE_INT = 'int';

    public const string TYPE_FLOAT = 'float';

    public const string TYPE_BOOL = 'bool';

    public const string TYPE_ARRAY = 'array';

    private const array VALID_TYPES = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_BOOL,
        self::TYPE_ARRAY,
    ];

    /**
     * @param  string  $path  Dot-separated key path, e.g. "manifest.cache_ttl_seconds"
     * @param  string  $type  One of the TYPE_* constants
     * @param  bool  $required  When true, absence throws ConfigurationKeyMissing
     * @param  mixed  $default  Used when required=false and no source provides the key.
     *                          Ignored when required=true.
     */
    public function __construct(
        public string $path,
        public string $type,
        public bool $required = true,
        public mixed $default = null,
    ) {
        if (trim($path) === '') {
            throw new InvalidArgumentException('Configuration key path must be a non-empty string.');
        }

        if (! in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid configuration key type "%s". Must be one of: %s.',
                $type,
                implode(', ', self::VALID_TYPES),
            ));
        }
    }

    public function isOptional(): bool
    {
        return ! $this->required;
    }

    public function hasDefault(): bool
    {
        return ! $this->required && $this->default !== null;
    }
}
