<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Generator\MethodGenerator as ProxyManagerLtsMethodGenerator;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLts\Proxy\GhostObjectInterface;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLts\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLts\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManagerLts\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     *
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = []): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $filteredProperties = Properties::fromReflectionClass($originalClass)
            ->filter($proxyOptions['skippedProperties'] ?? []);

        $publicProperties    = new PublicPropertiesMap($filteredProperties);
        $privateProperties   = new PrivatePropertiesMap($filteredProperties);
        $protectedProperties = new ProtectedPropertiesMap($filteredProperties);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([GhostObjectInterface::class]);
        $classGenerator->addPropertyFromGenerator($initializer           = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer($initializer, $initializationTracker, $filteredProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                $this->getAbstractProxiedMethods($originalClass),
                [
                    $init,
                    new StaticProxyConstructor($initializer, $filteredProperties),
                    new MagicGet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties,
                        $initializationTracker
                    ),
                    new MagicSet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicClone($originalClass, $initializer, $init),
                    new MagicSleep($originalClass, $initializer, $init),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ]
            )
        );
    }

    /**
     * Retrieves all abstract methods to be proxied
     *
     * @return MethodGenerator[]
     */
    private function getAbstractProxiedMethods(ReflectionClass $originalClass): array
    {
        return array_map(
            static function (ReflectionMethod $method): ProxyManagerLtsMethodGenerator {
                $generated = ProxyManagerLtsMethodGenerator::fromReflectionWithoutBodyAndDocBlock(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                );

                $generated->setAbstract(false);

                return $generated;
            },
            ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass)
        );
    }
}
