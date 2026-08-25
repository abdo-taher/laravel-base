<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Contracts;

use Base\Platform\Media\Public\Exceptions\MediaUploadFailed;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaReference;

interface MediaUploader
{
    /**
     * @param  mixed  $stream  A file stream or resource.
     * @param  string  $originalName  Original filename provided by the client.
     * @param  MediaAccessScope  $scope  The access scope identifying the upload session.
     * @return MediaReference The generated opaque reference.
     *
     * @throws MediaUploadFailed
     */
    public function upload(
        mixed $stream,
        string $originalName,
        MediaAccessScope $scope
    ): MediaReference;
}
