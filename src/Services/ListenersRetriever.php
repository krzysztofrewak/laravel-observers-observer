<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Events\Dispatcher;
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
        foreach ($this->getListeners() as $key => $observer) {
            $listeners->add($key);
        }

        return $listeners->filter(function (string $listener): bool {
            return Str::startsWith($listener, static::ELOQUENT_EVENT_PREFIX);
        });
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    protected function getListeners(): array
    {
        $reflectedDispatcher = new ReflectionClass(Dispatcher::class);

        $reflectedListeners = $reflectedDispatcher->getProperty("listeners");
        $reflectedListeners->setAccessible(true);

        return $reflectedListeners->getValue($this->dispatcher);
    }
}