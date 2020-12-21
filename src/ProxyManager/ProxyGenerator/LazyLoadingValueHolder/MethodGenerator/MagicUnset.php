<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__unset` method for lazy loading value holder objects
 */
class MagicUnset extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__unset', [new ParameterGenerator('name')]);

        $hasParent   = $originalClass->hasMethod('__unset');
        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = '';

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    unset($this->' . $valueHolder . '->$name);' . "\n\n    return;"
                . "\n}\n\n";
        }

        $callParent .= $hasParent
            ? 'return $this->' . $valueHolder . '->__unset($name);'
            : PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'name',
                null,
                $valueHolderProperty,
                null,
                $originalClass->isInterface() ? $originalClass->getName() : null
            );

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder . ', $this, \'__unset\', array(\'name\' => $name), $this->'
            . $initializer . ') || 1) && $this->' . $valueHolder . ' = $' . $valueHolder . ';' . "\n\n" . $callParent
        );
    }
}
