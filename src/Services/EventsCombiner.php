<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Support\Collection;
use ReflectionClass;

/**
 * Class EventsCombiner
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class EventsCombiner
{
    /**
     * @param Collection $listeners
     * @param Collection $models
     * @return Collection
     */
    public function combine(Collection $listeners, Collection $models): Collection
    {
        $results = $listeners->map(function (string $model): array {
            $data = explode(": ", $model);
            return $this->buildResult($data[1], "anonymous", $data[0]);
        });

        $models->each(function (string $model) use ($results): void {
            $reflectedModel = new ReflectionClass($model);
            $reflectedEvents = $reflectedModel->getProperty("dispatchesEvents");
            $reflectedEvents->setAccessible(true);

            $events = $reflectedEvents->getValue(new $model());
            foreach ($events as $trigger => $event) {
                $results->add($this->buildResult($model, $event, $trigger));
            }

            if (!$results->where("model", $model)->count()) {
                $results->add($this->buildResult($model));
            }
        });

        return $results->sortBy("index")->map(function (array $result): array {
            unset($result["index"]);
            return $result;
        });
    }

    /**
     * @param string $model
     * @param string $event
     * @param string $trigger
     * @return array
     */
    protected function buildResult(string $model, string $event = "", string $trigger = ""): array
    {
        return [
            "model" => $model,
            "event" => $event,
            "trigger" => $trigger,
            "index" => "$model@$trigger@$event",
        ];
    }
}