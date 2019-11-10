<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KrzysztofRewak\ObserversObserver\Services\EventsCombiner;
use KrzysztofRewak\ObserversObserver\Services\ListenersRetriever;
use KrzysztofRewak\ObserversObserver\Services\ModelsRetriever;
use KrzysztofRewak\ObserversObserver\Services\RegisteredEvents;
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
    protected $description = "Lists all model-related events";
    /** @var Filesystem */
    protected $filesystem;
    /** @var ModelsRetriever */
    protected $modelsRetriever;
    /**  @var ListenersRetriever */
    protected $listenersRetriever;
    /**  @var EventsCombiner */
    protected $combiner;

    /**
     * ListObservers constructor.
     * @param Filesystem $filesystem
     * @param ModelsRetriever $modelsRetriever
     * @param ListenersRetriever $listenersRetriever
     * @param EventsCombiner $combiner
     */
    public function __construct(Filesystem $filesystem, ModelsRetriever $modelsRetriever, ListenersRetriever $listenersRetriever, EventsCombiner $combiner)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->modelsRetriever = $modelsRetriever;
        $this->listenersRetriever = $listenersRetriever;
        $this->combiner = $combiner;
    }

    /**
     * Handler will fetch all files, check which of them are Models and then assign them to retrieved events
     * @throws ReflectionException
     */
    public function handle(): void
    {
        $this->info("Counting and analyzing application and framework files...");

        $files = $this->filesystem->allFiles(".");
        $count = count($files);
        $this->info("$count files found.");

        $models = $this->modelsRetriever->retrieve($files);
        $this->info("{$models->count()} model files found.");

        $listeners = $this->listenersRetriever->retrieve();
        $this->info("{$listeners->count()} events found.");

        $results = $this->combiner->combine($listeners, $models);
        $this->table(["Model", "Event", "Triggered on", "Notes"], $results);
    }
}