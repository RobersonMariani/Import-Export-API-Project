<?php

declare(strict_types=1);

use App\Api\Modules\Auth\Controllers\AuthController;
use App\Api\Modules\Export\Controllers\ExportController;
use App\Api\Modules\Health\Controllers\HealthController;
use App\Api\Modules\Health\Controllers\MetricsController;
use App\Api\Modules\Import\Controllers\ImportController;
use App\Api\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth — 10/min por IP (proteção brute force)
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function (): void {
        // Auth autenticado — api-write (60/min por user)
        Route::middleware('throttle:api-write')->group(function (): void {
            Route::post('/auth/refresh', [AuthController::class, 'refresh']);
            Route::post('/auth/logout', [AuthController::class, 'logout']);
        });

        // Auth leitura — api-read (120/min por user)
        Route::middleware('throttle:api-read')->group(function (): void {
            Route::get('/auth/me', [AuthController::class, 'me']);
        });

        // User routes — leitura
        Route::middleware('throttle:api-read')->group(function (): void {
            Route::get('users/count', [UserController::class, 'count']);
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{user}', [UserController::class, 'show']);
        });

        // User routes — escrita
        Route::middleware('throttle:api-write')->group(function (): void {
            Route::post('users', [UserController::class, 'store']);
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);
        });

        // Import routes — feature flag
        Route::middleware('feature:import')->group(function (): void {
            Route::middleware('throttle:api-read')->group(function (): void {
                Route::get('/imports', [ImportController::class, 'index']);
                Route::get('/imports/{import}', [ImportController::class, 'show']);
            });

            Route::middleware('throttle:api-upload')->group(function (): void {
                Route::post('/imports', [ImportController::class, 'store']);
            });

            Route::middleware('throttle:api-write')->group(function (): void {
                Route::delete('/imports/{import}', [ImportController::class, 'destroy']);
                Route::post('/imports/{import}/retry', [ImportController::class, 'retry']);
            });
        });

        // Export routes — feature flag
        Route::middleware('feature:export')->group(function (): void {
            Route::middleware('throttle:api-read')->group(function (): void {
                Route::get('/exports', [ExportController::class, 'index']);
                Route::get('/exports/{export}', [ExportController::class, 'show']);
                Route::get('/exports/{export}/download', [ExportController::class, 'download']);
            });

            Route::middleware('throttle:api-upload')->group(function (): void {
                Route::post('/exports', [ExportController::class, 'store']);
            });

            Route::middleware('throttle:api-write')->group(function (): void {
                Route::delete('/exports/{export}', [ExportController::class, 'destroy']);
                Route::post('/exports/{export}/retry', [ExportController::class, 'retry']);
            });
        });
    });

    // Health/Metrics — 60/min por IP (monitoramento)
    Route::middleware('throttle:monitoring')->group(function (): void {
        Route::get('/health', [HealthController::class, 'check']);
        Route::get('/metrics', [MetricsController::class, 'index']);
    });
});
