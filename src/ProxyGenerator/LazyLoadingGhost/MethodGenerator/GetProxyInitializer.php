<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManagerLts\Proxy\LazyLoadingInterface::getProxyInitializer}
 * for lazy loading value holder objects
 */
class GetProxyInitializer extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct('getProxyInitializer');
        $this->setReturnType('?\\Closure');
        $this->setBody('return $this->' . $initializerProperty->getName() . ';');
    }
}
