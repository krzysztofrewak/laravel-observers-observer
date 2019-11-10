<?php

namespace KrzysztofRewak\ObserversObserver\Services;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;

/**
 * Class ExtendedDispatcher
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class ExtendedDispatcher extends Dispatcher
{
    /**
     * @param array|string $events
     * @param mixed $listener
     */
    public function listen($events, $listener)
    {
        $debug = [];
        $i = 0;

        while (true) {
            if (!isset(debug_backtrace()[$i]["class"]) || is_subclass_of(debug_backtrace()[$i]["class"], ModelsRetriever::MODEL_CLASS)) {
                $debug = debug_backtrace()[$i - 1];
                break;
            }
            $i++;
        }

        if (isset($debug["file"])) {
            $events .= "@" . $debug["file"] . ":" . $debug["line"];
        }

        parent::listen($events, $listener);
    }
}