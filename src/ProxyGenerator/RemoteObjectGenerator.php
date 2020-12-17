<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\RemoteObjectInterface;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\RemoteObjectInterface}
 *
 * {@inheritDoc}
 */
class RemoteObjectGenerator implements ProxyGeneratorInterface
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

        $interfaces = [RemoteObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($adapter = new AdapterProperty());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    static function (ReflectionMethod $method) use ($adapter, $originalClass): RemoteObjectMethod {
                        return RemoteObjectMethod::generateMethod(
                            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                            $adapter,
                            $originalClass
                        );
                    },
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass, $adapter),
                    new MagicGet($originalClass, $adapter),
                    new MagicSet($originalClass, $adapter),
                    new MagicIsset($originalClass, $adapter),
                    new MagicUnset($originalClass, $adapter),
                ]
            )
        );
    }
}
