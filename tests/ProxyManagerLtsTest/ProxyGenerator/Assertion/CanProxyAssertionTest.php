<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\Assertion;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerLtsTestAsset\AccessInterceptorValueHolderMock;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\CallableTypeHintClass;
use ProxyManagerLtsTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerLtsTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithFinalMethods;
use ProxyManagerLtsTestAsset\ClassWithMethodWithDefaultParameters;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithParentHint;
use ProxyManagerLtsTestAsset\ClassWithPrivateProperties;
use ProxyManagerLtsTestAsset\ClassWithProtectedProperties;
use ProxyManagerLtsTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerLtsTestAsset\ClassWithPublicProperties;
use ProxyManagerLtsTestAsset\ClassWithSelfHint;
use ProxyManagerLtsTestAsset\EmptyClass;
use ProxyManagerLtsTestAsset\FinalClass;
use ProxyManagerLtsTestAsset\HydratedObject;
use ProxyManagerLtsTestAsset\LazyLoadingMock;
use ProxyManagerLtsTestAsset\NullObjectMock;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\Assertion\CanProxyAssertion
 * @group Coverage
 */
final class CanProxyAssertionTest extends TestCase
{
    public function testDeniesFinalClasses(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(FinalClass::class));
    }

    public function testDeniesClassesWithAbstractProtectedMethods(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            ClassWithAbstractProtectedMethod::class
        ));
    }

    public function testAllowsInterfaceByDefault(): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            BaseInterface::class
        ));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDeniesInterfaceIfSpecified(): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseClass::class), false);

        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseInterface::class), false);
    }

    /**
     * @dataProvider validClasses
     * @psalm-param class-string $className
     */
    public function testAllowedClass(string $className): void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass($className));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDisallowsConstructor(): void
    {
        $this->expectException(BadMethodCallException::class);

        new CanProxyAssertion();
    }

    /**
     * @return string[][]
     */
    public function validClasses(): array
    {
        return [
            [AccessInterceptorValueHolderMock::class],
            [BaseClass::class],
            [BaseInterface::class],
            [CallableTypeHintClass::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithFinalMagicMethods::class],
            [ClassWithFinalMethods::class],
            [ClassWithMethodWithDefaultParameters::class],
            [ClassWithMixedProperties::class],
            [ClassWithPrivateProperties::class],
            [ClassWithProtectedProperties::class],
            [ClassWithPublicProperties::class],
            [ClassWithPublicArrayProperty::class],
            [ClassWithSelfHint::class],
            [ClassWithParentHint::class],
            [EmptyClass::class],
            [HydratedObject::class],
            [LazyLoadingMock::class],
            [NullObjectMock::class],
        ];
    }
}
