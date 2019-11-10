<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver;

use Illuminate\Contracts\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use KrzysztofRewak\ObserversObserver\Services\ExtendedDispatcher;

/**
 * Class ObserversObserverServiceProvider
 * @package KrzysztofRewak\ObserversObserver
 */
class ObserversObserverServiceProvider extends ServiceProvider
{
    /** @var string */
    protected const CONFIG_FILE_PATH = __DIR__ . "/../config/observers.php";

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ListObservers::class,
            ]);

            $this->publishes([
                static::CONFIG_FILE_PATH => config_path("observers.php"),
            ]);
        }
    }

    /**
     * Register library services.
     */
    public function register()
    {
        if ($this->app->runningInConsole() && $this->isCommandFired("observers:list")) {
            $this->app->extend(Dispatcher::class, function (Dispatcher $dispatcher, Container $app): Dispatcher {
                return new ExtendedDispatcher($app);
            });

            $this->mergeConfigFrom(static::CONFIG_FILE_PATH, "observers");
        }
    }

    /**
     * @param string $command
     * @return bool
     */
    protected function isCommandFired(string $command): bool
    {
        return $this->app->request->server()["argv"][1] === $command;
    }
}