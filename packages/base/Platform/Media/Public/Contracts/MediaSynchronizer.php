<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Contracts;

use Base\Platform\Media\Public\Exceptions\MediaSlotViolation;
use Base\Platform\Media\Public\ValueObjects\MediaAccessScope;
use Base\Platform\Media\Public\ValueObjects\MediaOwnerReference;
use Base\Platform\Media\Public\ValueObjects\MediaSlotChanges;
use Base\Platform\Media\Public\ValueObjects\MediaSlotDefinition;

interface MediaSynchronizer
{
    /**
     * @param  MediaOwnerReference  $owner  The entity owning the media attachments.
     * @param  MediaAccessScope  $scope  The current session scope to authorize new attachments.
     * @param  MediaSlotChanges  $changes  The changes applied to the slots.
     * @param  list<MediaSlotDefinition>  $slotDefinitions  Rules governing slots.
     *
     * @throws MediaSlotViolation
     */
    public function sync(
        MediaOwnerReference $owner,
        MediaAccessScope $scope,
        MediaSlotChanges $changes,
        array $slotDefinitions
    ): void;
}
