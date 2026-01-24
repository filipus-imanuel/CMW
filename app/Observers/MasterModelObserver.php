<?php

namespace App\Observers;

use App\Helpers\CMW\PopulateDataHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic observer for master models to auto-clear PopulateDataHelper cache.
 *
 * Usage in AppServiceProvider:
 * Model::observe(MasterModelObserver::class);
 */
class MasterModelObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Clear cache for the model.
     */
    private function clearCache(Model $model): void
    {
        PopulateDataHelper::clearCache(get_class($model));
    }
}
