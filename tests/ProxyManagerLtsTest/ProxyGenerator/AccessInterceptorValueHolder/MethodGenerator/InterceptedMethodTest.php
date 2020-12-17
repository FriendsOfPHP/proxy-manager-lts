<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\MethodGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use ProxyManagerLtsTestAsset\BaseClass;

use function strpos;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod::generateMethod
 * @group Coverage
 */
final class InterceptedMethodTest extends TestCase
{
    public function testBodyStructure(): void
    {
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $method = InterceptedMethod::generateMethod(
            new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod'),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertGreaterThan(
            0,
            strpos(
                $method->getBody(),
                '$returnValue = $this->foo->publicByReferenceParameterMethod($param, $byRefParam);'
            )
        );
    }
}
