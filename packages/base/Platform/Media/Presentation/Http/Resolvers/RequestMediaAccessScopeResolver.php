<?php

declare(strict_types=1);

namespace Base\Platform\Media\Presentation\Http\Resolvers;

use Base\Platform\Media\Presentation\Contracts\MediaAccessScopeResolver;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

final class RequestMediaAccessScopeResolver implements MediaAccessScopeResolver
{
    public function resolve(Request $request): MediaAccessScope
    {
        $user = $request->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return MediaAccessScope::fromString('principal:'.$user->getAuthIdentifier());
    }
}
