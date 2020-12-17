<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManagerLts\Factory\AccessInterceptorValueHolderFactory;
use ProxyManagerLts\Factory\LazyLoadingGhostFactory;
use ProxyManagerLts\Factory\LazyLoadingValueHolderFactory;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerLtsTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithFinalMethods;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerLtsTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithParentHint;
use ProxyManagerLtsTestAsset\ClassWithPrivateProperties;
use ProxyManagerLtsTestAsset\ClassWithProtectedProperties;
use ProxyManagerLtsTestAsset\ClassWithPublicProperties;
use ProxyManagerLtsTestAsset\ClassWithPublicStringNullableTypedProperty;
use ProxyManagerLtsTestAsset\ClassWithPublicStringTypedProperty;
use ProxyManagerLtsTestAsset\ClassWithSelfHint;
use ProxyManagerLtsTestAsset\EmptyClass;
use ProxyManagerLtsTestAsset\HydratedObject;
use ProxyManagerLtsTestAsset\IterableTypeHintClass;
use ProxyManagerLtsTestAsset\ObjectTypeHintClass;
use ProxyManagerLtsTestAsset\ReturnTypeHintedClass;
use ProxyManagerLtsTestAsset\ScalarTypeHintedClass;
use ProxyManagerLtsTestAsset\VoidMethodTypeHintedClass;

use function get_class;

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
        if (null === $object && \PHP_VERSION_ID < 70400) {
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

        if ($className !== ClassWithMixedTypedProperties::class) {
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
        return [
            [new BaseClass()],
            [new ClassWithMagicMethods()],
            [new ClassWithFinalMethods()],
            [new ClassWithFinalMagicMethods()],
            [new ClassWithByRefMagicMethods()],
            [new ClassWithMixedProperties()],
            [\PHP_VERSION_ID >= 70400 ? new ClassWithMixedTypedProperties() : null],
            [\PHP_VERSION_ID >= 70400 ? new ClassWithMixedReferenceableTypedProperties() : null],
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
    }
}
