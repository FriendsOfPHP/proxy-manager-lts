<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Closure;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
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
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct('setProxyInitializer');

        $initializerParameter = new ParameterGenerator('initializer');

        $initializerParameter->setType(Closure::class);
        $initializerParameter->setDefaultValue(null);
        $this->setParameter($initializerParameter);
        $this->setBody('$this->' . $initializerProperty->getName() . ' = $initializer;');
        $this->setReturnType('void');
    }
}
