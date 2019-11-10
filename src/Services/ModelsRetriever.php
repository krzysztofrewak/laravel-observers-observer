<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class ModelsRetriever
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class ModelsRetriever
{
    /** @var string */
    public const MODEL_CLASS = "Illuminate\Database\Eloquent\Model";

    /**
     * @param array $files
     * @return Collection
     */
    public function retrieve(array $files): Collection
    {
        $models = collect();

        foreach ($files as $file) {
            $file = (string)$file;
            if ($this->checkIfFileIsPhp($file) && !$this->checkIfFileIsOnBlacklist($file)) {
                try {
                    $class = ClassNameBuilder::getClassFromFile($file);
                    if ($class && $this->checkIfClassIsModel($class)) {
                        $models->add($class);
                        $class::observe(null);
                    }
                } catch (Throwable $exception) {
                    continue;
                }
            }
        }

        return $models;
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function checkIfFileIsPhp(string $file): bool
    {
        return Str::endsWith($file, ".php");
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function checkIfFileIsOnBlacklist(string $file): bool
    {
        return Str::contains($file, config("observers.blacklist"));
    }

    /**
     * @param string $class
     * @return bool
     */
    protected function checkIfClassIsModel(string $class)
    {
        return is_subclass_of($class, static::MODEL_CLASS);
    }
}