<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MagicMethodGenerator;
use ReflectionClass;

use function var_export;

/**
 * Magic `__sleep` for value holder objects
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $valueHolderProperty)
    {
        parent::__construct($originalClass, '__sleep');

        $this->setBody('return array(' . var_export($valueHolderProperty->getName(), true) . ');');
    }
}
