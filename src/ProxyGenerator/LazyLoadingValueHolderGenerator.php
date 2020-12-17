<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\VirtualProxyInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSleep;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingValueHolderGenerator implements ProxyGeneratorInterface
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

        $interfaces       = [VirtualProxyInterface::class];
        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildLazyLoadingMethodInterceptor($initializer, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    new StaticProxyConstructor($initializer, Properties::fromReflectionClass($originalClass)),
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new MagicGet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicSet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicIsset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicUnset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicClone($originalClass, $initializer, $valueHolder),
                    new MagicSleep($originalClass, $initializer, $valueHolder),
                    new MagicWakeup($originalClass),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $valueHolder),
                    new IsProxyInitialized($valueHolder),
                    new GetWrappedValueHolderValue($valueHolder),
                ]
            )
        );
    }

    private function buildLazyLoadingMethodInterceptor(
        InitializerProperty $initializer,
        ValueHolderProperty $valueHolder
    ): callable {
        return static function (ReflectionMethod $method) use ($initializer, $valueHolder): LazyLoadingMethodInterceptor {
            return LazyLoadingMethodInterceptor::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $initializer,
                $valueHolder
            );
        };
    }
}
