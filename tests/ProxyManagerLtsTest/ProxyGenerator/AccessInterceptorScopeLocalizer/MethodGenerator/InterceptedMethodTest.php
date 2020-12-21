<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\MethodGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\ClassWithMethodWithVariadicFunction;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod
 * @group Coverage
 */
final class InterceptedMethodTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private $prefixInterceptors;

    /** @var PropertyGenerator&MockObject */
    private $suffixInterceptors;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->method('getName')->willReturn('pre');
        $this->suffixInterceptors->method('getName')->willReturn('post');
    }

    public function testBodyStructure(): void
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertStringMatchesFormat(
            '%a$returnValue = parent::publicByReferenceParameterMethod($param, $byRefParam);%A',
            $method->getBody()
        );
    }

    public function testForwardsVariadicParameters(): void
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(ClassWithMethodWithVariadicFunction::class, 'foo'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame('foo', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertStringMatchesFormat(
            '%a$returnValue = parent::foo($bar, ...$baz);%A',
            $method->getBody()
        );
    }
}
