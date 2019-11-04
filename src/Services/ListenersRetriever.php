<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;

/**
 * Class ListenersRetriever
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class ListenersRetriever
{
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

        return $listeners;
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