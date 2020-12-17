<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use ReflectionClass;

/**
 * Base interface for proxy generators - describes how a proxy generator should use
 * reflection classes to modify given class generators
 */
interface ProxyGeneratorInterface
{
    /**
     * Apply modifications to the provided $classGenerator to proxy logic from $originalClass
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []): void;
}
