<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\AccessInterceptorValueHolderInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicClone;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\ValueHolderInterface}
 * and {@see \ProxyManagerLts\Proxy\AccessInterceptorInterface}
 *
 * {@inheritDoc}
 */
class AccessInterceptorValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));
        $interfaces       = [AccessInterceptorValueHolderInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder        = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new StaticProxyConstructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new GetWrappedValueHolderValue($valueHolder),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicSet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $valueHolder),
                    new MagicWakeup($originalClass),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixes,
        MethodSuffixInterceptors $suffixes,
        ValueHolderProperty $valueHolder
    ): callable {
        return static function (ReflectionMethod $method) use ($prefixes, $suffixes, $valueHolder): InterceptedMethod {
            return InterceptedMethod::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $valueHolder,
                $prefixes,
                $suffixes
            );
        };
    }
}
