<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Public\Exceptions;

final class DeviceNotFound extends DeviceException
{
    public function __construct()
    {
        parent::__construct('Device not found.');
    }
}
