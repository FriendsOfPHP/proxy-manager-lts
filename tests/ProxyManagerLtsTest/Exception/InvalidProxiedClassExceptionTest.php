<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerLtsTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerLtsTestAsset\ClassWithProtectedMethod;
use ProxyManagerLtsTestAsset\FinalClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\Exception\InvalidProxiedClassException}
 *
 * @covers \ProxyManagerLts\Exception\InvalidProxiedClassException
 * @group Coverage
 */
final class InvalidProxiedClassExceptionTest extends TestCase
{
    public function testInterfaceNotSupported(): void
    {
        self::assertSame(
            'Provided interface "ProxyManagerLtsTestAsset\BaseInterface" cannot be proxied',
            InvalidProxiedClassException::interfaceNotSupported(
                new ReflectionClass(BaseInterface::class)
            )->getMessage()
        );
    }

    public function testFinalClassNotSupported(): void
    {
        self::assertSame(
            'Provided class "ProxyManagerLtsTestAsset\FinalClass" is final and cannot be proxied',
            InvalidProxiedClassException::finalClassNotSupported(
                new ReflectionClass(FinalClass::class)
            )->getMessage()
        );
    }

    public function testAbstractProtectedMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "ProxyManagerLtsTestAsset\ClassWithAbstractProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n"
            . 'ProxyManagerLtsTestAsset\ClassWithAbstractProtectedMethod::protectedAbstractMethod',
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testProtectedMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "ProxyManagerLtsTestAsset\ClassWithProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testAbstractPublicMethodsNotSupported(): void
    {
        self::assertSame(
            'Provided class "ProxyManagerLtsTestAsset\ClassWithAbstractPublicMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractPublicMethod::class)
            )->getMessage()
        );
    }
}
