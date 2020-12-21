<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\NullObject\MethodGenerator;

use Laminas\Code\Reflection\MethodReflection;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use ProxyManagerLtsTestAsset\BaseClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor}
 *
 * @group Coverage
 */
final class NullObjectMethodInterceptorTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructure(): void
    {
        $reflection = new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod');
        $method     = NullObjectMethodInterceptor::generateMethod($reflection);

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertSame('', $method->getBody());
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutParameters(): void
    {
        $reflectionMethod = new MethodReflection(self::class, 'testBodyStructureWithoutParameters');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        self::assertSame('testBodyStructureWithoutParameters', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertSame('', $method->getBody());
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutByRefReturn(): void
    {
        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicByReferenceMethod');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        self::assertSame('publicByReferenceMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertStringMatchesFormat("\$ref%s = null;\nreturn \$ref%s;", $method->getBody());
    }
}
