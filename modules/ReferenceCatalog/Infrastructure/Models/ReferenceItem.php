<?php

declare(strict_types=1);

namespace Modules\ReferenceCatalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 */
final class ReferenceItem extends Model
{
    use HasUuids;

    protected $table = 'reference_items';

    protected $fillable = [
        'name',
    ];
}
