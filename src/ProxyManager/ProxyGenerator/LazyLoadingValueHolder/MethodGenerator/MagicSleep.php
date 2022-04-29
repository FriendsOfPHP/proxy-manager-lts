<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;

use function var_export;

/**
 * Magic `__sleep` for lazy loading value holder objects
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) {
        parent::__construct($originalClass, '__sleep');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $reflectionMethod = $originalClass->hasMethod('__sleep')
            ? $originalClass->getMethod('__sleep')
            : null;
        
        if (\PHP_VERSION_ID > 80100 && $reflectionMethod && $reflectionMethod->hasReturnType()) {
            $this->setReturnType($reflectionMethod->getReturnType());
        }

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder . ', $this, \'__sleep\', array(), $this->'
            . $initializer . ') || 1) && $this->' . $valueHolder . ' = $' . $valueHolder . ';' . "\n\n"
            . 'return array(' . var_export($valueHolder, true) . ');'
        );
    }
}
