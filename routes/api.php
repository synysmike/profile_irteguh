<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;

Route::prefix('v1')->group(function () {
    Route::apiResource('profiles', ProfileController::class);
    Route::get('profiles/{id}/public', [ProfileController::class, 'showPublic']);
});
