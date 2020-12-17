<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\NullObjectInterface;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\NullObjectInterface}
 *
 * {@inheritDoc}
 */
class NullObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [NullObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass, []) as $method) {
            $classGenerator->addMethodFromGenerator(
                NullObjectMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        ClassGeneratorUtils::addMethodIfNotFinal(
            $originalClass,
            $classGenerator,
            new StaticProxyConstructor($originalClass)
        );
    }
}
