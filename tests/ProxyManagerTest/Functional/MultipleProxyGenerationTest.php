<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithTypedMagicMethods;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPhp80TypedMethods;
use ProxyManagerTestAsset\ClassWithPhp81Defaults;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithReadOnlyProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\IterableTypeHintClass;
use ProxyManagerTestAsset\ObjectTypeHintClass;
use ProxyManagerTestAsset\ReturnTypeHintedClass;
use ProxyManagerTestAsset\ScalarTypeHintedClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedClass;

use function get_class;
use function in_array;

use const PHP_VERSION_ID;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @link https://github.com/Ocramius/ProxyManager/issues/10
 *
 * @group Functional
 * @group issue-10
 * @coversNothing
 */
final class MultipleProxyGenerationTest extends TestCase
{
    /**
     * Verifies that proxies generated from different factories will retain their specific implementation
     * and won't conflict
     *
     * @dataProvider getTestedClasses
     */
    public function testCanGenerateMultipleDifferentProxiesForSameClass($object): void
    {
        if (null === $object && PHP_VERSION_ID < 70400) {
            self::markTestSkipped('PHP 7.4 required.');
        }

        $ghostProxyFactory                      = new LazyLoadingGhostFactory();
        $virtualProxyFactory                    = new LazyLoadingValueHolderFactory();
        $accessInterceptorFactory               = new AccessInterceptorValueHolderFactory();
        $accessInterceptorScopeLocalizerFactory = new AccessInterceptorScopeLocalizerFactory();
        $className                              = get_class($object);
        $initializer                            = static function (): bool {
            return true;
        };

        $generated = [
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy($object),
        ];

        if (! in_array($className, [ClassWithMixedTypedProperties::class, ClassWithReadOnlyProperties::class], true)) {
            $generated[] = $accessInterceptorScopeLocalizerFactory->createProxy($object);
        }

        foreach ($generated as $key => $proxy) {
            self::assertInstanceOf($className, $proxy);

            foreach ($generated as $comparedKey => $comparedProxy) {
                if ($comparedKey === $key) {
                    continue;
                }

                self::assertNotSame(get_class($comparedProxy), get_class($proxy));
            }

            $proxyClass = get_class($proxy);

            /**
             * @psalm-suppress InvalidStringClass
             * @psalm-suppress MixedMethodCall
             */
            self::assertInstanceOf($proxyClass, new $proxyClass(), 'Proxy can be instantiated via normal constructor');
        }
    }

    /**
     * @return object[][]
     */
    public function getTestedClasses(): array
    {
        $objects = [
            [new BaseClass()],
            [new ClassWithMagicMethods()],
            [new ClassWithFinalMethods()],
            [new ClassWithFinalMagicMethods()],
            [new ClassWithByRefMagicMethods()],
            [new ClassWithMixedProperties()],
            [PHP_VERSION_ID >= 70400 ? new ClassWithMixedTypedProperties() : null],
            [PHP_VERSION_ID >= 70400 ? new ClassWithMixedReferenceableTypedProperties() : null],
            //            [new ClassWithPublicStringTypedProperty()],
            //            [new ClassWithPublicStringNullableTypedProperty()],
            [new ClassWithPrivateProperties()],
            [new ClassWithProtectedProperties()],
            [new ClassWithPublicProperties()],
            [new EmptyClass()],
            [new HydratedObject()],
            [new ClassWithSelfHint()],
            [new ClassWithParentHint()],
            [new ClassWithCollidingPrivateInheritedProperties()],
            [new ClassWithMethodWithVariadicFunction()],
            [new ClassWithMethodWithByRefVariadicFunction()],
            [new ScalarTypeHintedClass()],
            [new IterableTypeHintClass()],
            [new ObjectTypeHintClass()],
            [new ReturnTypeHintedClass()],
            [new VoidMethodTypeHintedClass()],
        ];

        if (PHP_VERSION_ID >= 80000) {
            $objects[] = [new ClassWithPhp80TypedMethods()];
            $objects[] = [new ClassWithTypedMagicMethods()];
        }

        if (PHP_VERSION_ID >= 80100) {
            $objects['php81defaults'] = [new ClassWithPhp81Defaults()];
            $objects['readonly'] = [new ClassWithReadOnlyProperties()];
        }

        return $objects;
    }
}
