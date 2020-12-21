<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManagerLts\Proxy\LazyLoadingInterface::setProxyInitializer}
 * for lazy loading value holder objects
 */
class SetProxyInitializer extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct(
            'setProxyInitializer',
            [(new ParameterGenerator('initializer', 'Closure'))->setDefaultValue(null)],
            self::FLAG_PUBLIC,
            '$this->' . $initializerProperty->getName() . ' = $initializer;'
        );

        $this->setReturnType('void');
    }
}
