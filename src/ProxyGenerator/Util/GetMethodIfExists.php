<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\Util;

use ReflectionClass;
use ReflectionMethod;

/**
 * Internal utility class - allows fetching a method from a given class, if it exists
 */
final class GetMethodIfExists
{
    private function __construct()
    {
    }

    public static function get(ReflectionClass $class, string $method): ?ReflectionMethod
    {
        return $class->hasMethod($method) ? $class->getMethod($method) : null;
    }
}
