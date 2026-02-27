<?php

namespace App\Providers;

use App\Api\Modules\Import\Events\ChunkProcessedEvent;
use App\Api\Modules\Import\Events\ImportBatchCompletedEvent;
use App\Api\Modules\Import\Listeners\FinalizeImportListener;
use App\Api\Modules\Import\Listeners\UpdateImportProgressListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ChunkProcessedEvent::class, UpdateImportProgressListener::class);
        Event::listen(ImportBatchCompletedEvent::class, FinalizeImportListener::class);
    }
}
