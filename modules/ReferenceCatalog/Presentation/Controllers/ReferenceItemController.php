<?php

declare(strict_types=1);

namespace Modules\ReferenceCatalog\Presentation\Controllers;

use Base\Platform\Media\Presentation\Contracts\MediaAccessScopeResolver;
use Base\Platform\Media\Public\Exceptions\InvalidMediaReference;
use Base\Platform\Media\Public\Exceptions\MediaReferenceNotFound;
use Base\Platform\Media\Public\Exceptions\MediaSlotViolation;
use Base\Platform\Media\Public\ValueObjects\MediaReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReferenceCatalog\Application\ReferenceItemCreator;

final class ReferenceItemController extends Controller
{
    public function __construct(
        private readonly ReferenceItemCreator $creator,
        private readonly MediaAccessScopeResolver $scopeResolver
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cover' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string'],
        ]);

        $scope = $this->scopeResolver->resolve($request);

        try {
            $cover = isset($validated['cover']) && is_string($validated['cover'])
                ? MediaReference::fromString($validated['cover'])
                : null;

            $gallery = null;
            if (isset($validated['gallery']) && is_array($validated['gallery'])) {
                $gallery = array_values(array_map(function (mixed $ref): MediaReference {
                    return is_string($ref) ? MediaReference::fromString($ref) : throw new \InvalidArgumentException;
                }, $validated['gallery']));
            }

            $item = $this->creator->create($validated['name'], $scope, $cover, $gallery);

            return response()->json([
                'id' => $item->id,
                'name' => $item->name,
                // Do not expose internal persistence model fields for Media.
            ], 201);
        } catch (MediaSlotViolation|InvalidMediaReference|MediaReferenceNotFound $e) {
            return response()->json(['message' => 'Media validation failed: '.$e->getMessage()], 422);
        }
    }
}
