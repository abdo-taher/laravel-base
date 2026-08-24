<?php

declare(strict_types=1);

namespace Base\Foundation\CapabilityRegistry\Public\Contracts;

interface CapabilityProviderContract
{
    public function provide(): CapabilityContract;
}
