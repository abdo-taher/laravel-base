<?php

declare(strict_types=1);

namespace Base\Platform\Media\Infrastructure\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property string $storage_key
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property string $state
 * @property string $upload_scope
 * @property string|null $owner_type
 * @property string|null $owner_id
 * @property string|null $slot_name
 * @property int|null $sort_order
 * @property Carbon|null $attached_at
 * @property Carbon|null $orphaned_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class MediaItem extends Model
{
    public const STATE_TEMPORARY = 'TEMPORARY';

    public const STATE_ATTACHED = 'ATTACHED';

    public const STATE_ORPHANED = 'ORPHANED';

    protected $table = 'media';

    protected $guarded = [];

    protected $casts = [
        'attached_at' => 'datetime',
        'orphaned_at' => 'datetime',
    ];
}
