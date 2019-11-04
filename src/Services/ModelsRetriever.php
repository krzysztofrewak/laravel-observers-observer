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
    protected const MODEL_CLASS = "Illuminate\Database\Eloquent\Model";

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
                    $class = $this->getClassFromFile($file);
                    if ($class && is_subclass_of($class, static::MODEL_CLASS)) {
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
        return Str::contains($file, "vendor\\symfony\\");
    }

    /**
     * @param $path_to_file
     * @return mixed|string
     */
    protected function getClassFromFile($path_to_file)
    {
        $contents = file_get_contents($path_to_file);
        $namespace = $class = "";
        $getting_namespace = $getting_class = false;

        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $getting_namespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $getting_class = true;
            }

            if ($getting_namespace === true) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $token[1];
                } else {
                    if ($token === ";") {
                        $getting_namespace = false;
                    }
                }
            }

            if ($getting_class === true) {
                if (is_array($token) && $token[0] == T_STRING) {
                    $class = $token[1];
                    break;
                }
            }
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}