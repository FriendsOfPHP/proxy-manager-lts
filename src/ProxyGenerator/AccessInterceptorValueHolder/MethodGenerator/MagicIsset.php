<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MagicMethodGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\GetMethodIfExists;
use ProxyManagerLts\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__isset` for method interceptor value holder objects
 */
class MagicIsset extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $parent          = GetMethodIfExists::get($originalClass, '__isset');
        $valueHolderName = $valueHolder->getName();

        $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_ISSET,
            'name',
            'value',
            $valueHolder,
            'returnValue',
            $originalClass->isInterface() ? $originalClass->getName() : null
        );

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    $returnValue = isset($this->' . $valueHolderName . '->$name);'
                . "\n} else {\n    " . $callParent . "\n}\n\n";
        }

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $callParent,
            $this,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
