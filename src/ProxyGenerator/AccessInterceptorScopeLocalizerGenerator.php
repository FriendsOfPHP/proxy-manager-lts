<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\AccessInterceptorInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\ValueHolderInterface}
 * and localizing scope of the proxied object at instantiation
 *
 * {@inheritDoc}
 */
class AccessInterceptorScopeLocalizerGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([AccessInterceptorInterface::class]);
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors),
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset', '__clone', '__sleep']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass),
                    new BindProxyProperties($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicIsset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicUnset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicClone($originalClass, $prefixInterceptors, $suffixInterceptors),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixInterceptors,
        MethodSuffixInterceptors $suffixInterceptors
    ): callable {
        return static function (ReflectionMethod $method) use ($prefixInterceptors, $suffixInterceptors): InterceptedMethod {
            return InterceptedMethod::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $prefixInterceptors,
                $suffixInterceptors
            );
        };
    }
}
