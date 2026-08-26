<?php

declare(strict_types=1);

namespace Base\Platform\Media\Presentation\Http\Controllers;

use Base\Platform\Media\Presentation\Contracts\MediaAccessScopeResolver;
use Base\Platform\Media\Public\Contracts\MediaUploader;
use Base\Platform\Media\Public\Exceptions\MediaUploadFailed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;

final class MediaUploadController extends Controller
{
    public function __construct(
        private readonly MediaUploader $uploader,
        private readonly MediaAccessScopeResolver $scopeResolver,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $scope = $this->scopeResolver->resolve($request);
        $uploadedFile = $request->file('file');

        /** @var UploadedFile $uploadedFile */
        $stream = fopen($uploadedFile->getRealPath(), 'r+');

        if ($stream === false) {
            return response()->json(['message' => 'Unable to read upload stream.'], 422);
        }

        try {
            $reference = $this->uploader->upload(
                $stream,
                $uploadedFile->getClientOriginalName(),
                $scope
            );

            return response()->json([
                'reference' => $reference->value,
            ], 201);
        } catch (MediaUploadFailed $e) {
            return response()->json(['message' => 'Upload failed.'], 422);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
