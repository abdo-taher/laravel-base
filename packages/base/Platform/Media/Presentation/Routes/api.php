<?php

declare(strict_types=1);

use Base\Platform\Media\Presentation\Http\Controllers\MediaUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth'])->prefix('api')->group(function () {
    Route::post('/media', [MediaUploadController::class, 'store']);
});
