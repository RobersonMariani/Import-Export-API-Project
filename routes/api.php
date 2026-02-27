<?php

use App\Api\Modules\Auth\Controllers\AuthController;
use App\Api\Modules\Export\Controllers\ExportController;
use App\Api\Modules\Health\Controllers\HealthController;
use App\Api\Modules\Health\Controllers\MetricsController;
use App\Api\Modules\Import\Controllers\ImportController;
use App\Api\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth routes (Phase 1) — sem auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Auth routes — com auth:api
    Route::middleware('auth:api')->group(function (): void {
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // User routes (Phase 2)
        Route::apiResource('users', UserController::class);

        // Import routes (Phase 3) — feature flag
        Route::middleware('feature:import')->group(function (): void {
            Route::post('/imports', [ImportController::class, 'store']);
            Route::get('/imports', [ImportController::class, 'index']);
            Route::get('/imports/{import}', [ImportController::class, 'show']);
        });

        // Export routes (Phase 4) — feature flag
        Route::middleware('feature:export')->group(function (): void {
            Route::post('/exports', [ExportController::class, 'store']);
            Route::get('/exports/{export}', [ExportController::class, 'show']);
            Route::get('/exports/{export}/download', [ExportController::class, 'download']);
        });
    });

    // Health routes (Phase 5) — sem auth
    Route::get('/health', [HealthController::class, 'check']);
    Route::get('/metrics', [MetricsController::class, 'index']);
});
