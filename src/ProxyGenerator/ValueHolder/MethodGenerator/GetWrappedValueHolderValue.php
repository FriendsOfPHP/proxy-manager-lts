<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManagerLts\Proxy\ValueHolderInterface::getWrappedValueHolderValue}
 * for lazy loading value holder objects
 */
class GetWrappedValueHolderValue extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('getWrappedValueHolderValue');
        $this->setBody('return $this->' . $valueHolderProperty->getName() . ';');
    }
}
