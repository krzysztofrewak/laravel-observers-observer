<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver;

use Illuminate\Support\ServiceProvider;

/**
 * Class ObserversObserverServiceProvider
 * @package KrzysztofRewak\ObserversObserver
 */
class ObserversObserverServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ListObservers::class,
            ]);
        }
    }
}