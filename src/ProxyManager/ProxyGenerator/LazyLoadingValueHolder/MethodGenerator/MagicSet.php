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
 * Magic `__set` for lazy loading value holder objects
 */
class MagicSet extends MagicMethodGenerator
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
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $hasParent   = $originalClass->hasMethod('__set');
        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = '';

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    return ($this->' . $valueHolder . '->$name = $value);'
                . "\n}\n\n";
        }

        $callParent .= $hasParent
            ? 'return $this->' . $valueHolder . '->__set($name, $value);'
            : PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'name',
                'value',
                $valueHolderProperty,
                null,
                $originalClass->isInterface() ? $originalClass->getName() : null
            );

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder . ', $this, '
            . '\'__set\', array(\'name\' => $name, \'value\' => $value), $this->' . $initializer . ') || 1)'
            . ' && $this->' . $valueHolder . ' = $' . $valueHolder . ';'
            . "\n\n" . $callParent
        );
    }
}
