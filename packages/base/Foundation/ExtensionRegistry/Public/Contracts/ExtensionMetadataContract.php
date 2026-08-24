<?php

declare(strict_types=1);

namespace Base\Foundation\ExtensionRegistry\Public\Contracts;

interface ExtensionMetadataContract
{
    public function extensionId(): string;

    public function extensionPoint(): string;

    public function contributionId(): string;

    public function priority(): int;
}
