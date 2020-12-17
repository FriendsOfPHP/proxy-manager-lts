<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MagicMethodGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use ProxyManagerLts\ProxyGenerator\Util\GetMethodIfExists;
use ProxyManagerLts\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__get` for lazy loading ghost objects
 */
class MagicGet extends MagicMethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__get', [new ParameterGenerator('name')]);

        $parent = GetMethodIfExists::get($originalClass, '__get');

        $callParent = '$returnValue = & parent::__get($name);';

        if (! $parent) {
            $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'name',
                null,
                null,
                'returnValue',
                $originalClass->isInterface() ? $originalClass->getName() : null
            );
        }

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $callParent,
            $this,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
