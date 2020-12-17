<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManagerLts\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading ghost objects
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, ZendMethodGenerator $callInitializer)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $this->setBody(
            'return $this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'initializeProxy\', []);'
        );
    }
}
