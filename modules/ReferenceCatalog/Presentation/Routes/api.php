<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ReferenceCatalog\Presentation\Controllers\ReferenceItemController;

Route::middleware(['api', 'auth'])->prefix('api')->group(function () {
    Route::post('/reference-items', [ReferenceItemController::class, 'store']);
});
