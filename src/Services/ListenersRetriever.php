<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

/**
 * Class ListenersRetriever
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class ListenersRetriever
{
    /** @var string */
    protected const ELOQUENT_EVENT_PREFIX = "eloquent.";

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * ListenersRetriever constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return Collection
     * @throws ReflectionException
     */
    public function retrieve(): Collection
    {
        $listeners = collect();
        foreach ($this->getListeners() as $key => $events) {
            foreach ($events as $event) {
                $listeners->add($key);
            }
        }

        return $listeners->filter(function (string $listener): bool {
            return Str::startsWith($listener, static::ELOQUENT_EVENT_PREFIX);
        })->map(function(string $listener): string {
            return str_replace(static::ELOQUENT_EVENT_PREFIX, "", $listener);
        });
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    protected function getListeners(): array
    {
        $reflectedDispatcher = new ReflectionClass(get_class($this->dispatcher));
        $reflectedListeners = $reflectedDispatcher->getProperty("listeners");
        $reflectedListeners->setAccessible(true);

        return $reflectedListeners->getValue($this->dispatcher);
    }
}