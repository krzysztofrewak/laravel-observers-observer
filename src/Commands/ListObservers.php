<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KrzysztofRewak\ObserversObserver\Services\ListenersRetriever;
use KrzysztofRewak\ObserversObserver\Services\ModelsRetriever;
use ReflectionException;

/**
 * Class ListObservers
 * @package KrzysztofRewak\ObserversObserver\Commands
 */
class ListObservers extends Command
{
    /** @var string */
    protected $name = "observers:list";
    /** @var string */
    protected $description = "Lists observers";
    /** @var Filesystem */
    protected $filesystem;
    /** @var ModelsRetriever */
    protected $modelsRetriever;
    /**  @var ListenersRetriever */
    protected $listenersRetriever;

    /**
     * ListObservers constructor.
     * @param Filesystem $filesystem
     * @param ModelsRetriever $modelsRetriever
     * @param ListenersRetriever $listenersRetriever
     */
    public function __construct(Filesystem $filesystem, ModelsRetriever $modelsRetriever, ListenersRetriever $listenersRetriever)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->modelsRetriever = $modelsRetriever;
        $this->listenersRetriever = $listenersRetriever;
    }

    /**
     * Handler will fetch all files, check which of them are Models and then assign them to retrieved events
     * @throws ReflectionException
     */
    public function handle(): void
    {
        $this->info("Counting and analyzing application and framework files...");

        $files = $this->filesystem->allFiles(".");
        $models = $this->modelsRetriever->retrieve($files);

        $count = count($files);
        $this->info("{$models->count()} model files found (of $count files).");

        $this->table(["Namespaced class", "Events"], $models->map(function (string $model): array {
            return [
                $model,
            ];
        })->toArray());

        $listeners = $this->listenersRetriever->retrieve();
        $this->table(["Events"], $listeners->map(function (string $model): array {
            return [
                $model,
            ];
        })->toArray());
    }
}