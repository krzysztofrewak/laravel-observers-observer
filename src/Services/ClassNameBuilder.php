<?php

declare(strict_types = 1);

namespace KrzysztofRewak\ObserversObserver\Services;

/**
 * Class ClassNameBuilder
 * @package KrzysztofRewak\ObserversObserver\Services
 */
class ClassNameBuilder
{
    /**
     * @param $filePath
     * @return string
     *
     * Thanks for Jarret Byrne for solution:
     * More info in blog post: http://jarretbyrne.com/2015/06/197/
     */
    public static function getClassFromFile($filePath): string
    {
        $contents = file_get_contents($filePath);
        $namespace = $class = "";
        $gettingNamespace = $gettingClass = false;

        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $gettingNamespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $gettingClass = true;
            }

            if ($gettingNamespace === true) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $token[1];
                } else {
                    if ($token === ";") {
                        $gettingNamespace = false;
                    }
                }
            }

            if ($gettingClass === true) {
                if (is_array($token) && $token[0] == T_STRING) {
                    $class = $token[1];
                    break;
                }
            }
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}