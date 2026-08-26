<?php

declare(strict_types=1);

namespace Base\Platform\Media\Presentation\Contracts;

use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Illuminate\Http\Request;

interface MediaAccessScopeResolver
{
    /**
     * Resolves the access scope for the current upload request.
     */
    public function resolve(Request $request): MediaAccessScope;
}
