<?php

declare(strict_types=1);

namespace App\Providers;

use App\Api\Modules\Import\Events\ChunkProcessedEvent;
use App\Api\Modules\Import\Events\ImportBatchCompletedEvent;
use App\Api\Modules\Import\Listeners\FinalizeImportListener;
use App\Api\Modules\Import\Listeners\UpdateImportProgressListener;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(ChunkProcessedEvent::class, UpdateImportProgressListener::class);
        Event::listen(ImportBatchCompletedEvent::class, FinalizeImportListener::class);

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('api-read', function (Request $request) {
            return Limit::perMinute(2000)->by($request->user()?->getKey() ?: $request->ip());
        });

        RateLimiter::for('api-write', function (Request $request) {
            return Limit::perMinute(1000)->by($request->user()?->getKey() ?: $request->ip());
        });

        RateLimiter::for('api-upload', function (Request $request) {
            return Limit::perMinute(1000)->by($request->user()?->getKey() ?: $request->ip());
        });

        RateLimiter::for('monitoring', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
