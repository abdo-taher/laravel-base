<?php

declare(strict_types=1);

namespace Base\Platform\Media\Public\Contracts;

interface MediaCleaner
{
    /**
     * Scans for ORPHANED or TEMPORARY media records that have expired beyond TTL,
     * physically removes the blobs via files.storage, and deletes the persistence records.
     *
     * @param  int  $ttlSeconds  The time-to-live before eligible for cleanup.
     * @return int Number of records cleaned.
     */
    public function cleanExpired(int $ttlSeconds): int;
}
