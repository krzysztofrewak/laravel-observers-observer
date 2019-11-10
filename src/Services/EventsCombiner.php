<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Support\Collection;
use ReflectionClass;
use Throwable;

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
        $results = $this->mapListeners($listeners)->merge($this->mapModelEvents($models));
        $results = $this->tidyUpResults($results);

        return $results->sortBy("index")->map(function (array $result): array {
            unset($result["index"]);
            return $result;
        });
    }

    /**
     * @param string $model
     * @param string $event
     * @param string $trigger
     * @param string $notes
     * @return array
     */
    protected function buildResult(string $model, string $event = "", string $trigger = "", string $notes = ""): array
    {
        return [
            "model" => $model,
            "event" => $event,
            "trigger" => $trigger,
            "notes" => $notes,
            "index" => "$model@$trigger@$event",
        ];
    }

    /**
     * @param Collection $listeners
     * @return Collection
     */
    protected function mapListeners(Collection $listeners): Collection
    {
        return $listeners->map(function (string $model): array {
            $data = explode(": ", $model);
            $location = explode("@", $data[1]);

            $model = $location[0];
            $trigger = $data[0];
            $source = isset($location[1]) ? "in " . $location[1] : "via observer";

            return $this->buildResult($model, "anonymous", $trigger, $source);
        });
    }

    /**
     * @param Collection $models
     * @return Collection
     */
    protected function mapModelEvents(Collection $models): Collection
    {
        $results = collect();

        $models->each(function (string $model) use ($results): void {
            $reflectedModel = new ReflectionClass($model);
            $reflectedEvents = $reflectedModel->getProperty("dispatchesEvents");
            $reflectedEvents->setAccessible(true);

            try {
                $events = $reflectedEvents->getValue(new $model());
                foreach ($events as $trigger => $event) {
                    $results->add($this->buildResult($model, $event, $trigger, "via \$dispatchesEvents property"));
                }
            } catch (Throwable $exception) {
                $results->add($this->buildResult($model, "", "", "abstract class"));
            }

            if (!$results->where("model", $model)->count()) {
                $results->add($this->buildResult($model));
            }
        });

        return $results;
    }

    /**
     * @param Collection $results
     * @return Collection
     */
    protected function tidyUpResults(Collection $results): Collection
    {
        $multipleEventsModels = $results->groupBy("model")->filter(function (Collection $events): bool {
            return sizeof($events) > 1;
        })->keys()->toArray();

        return $results->reject(function (array $event) use($multipleEventsModels): bool {
            return in_array($event["model"], $multipleEventsModels) && !$event["event"];
        });
    }
}