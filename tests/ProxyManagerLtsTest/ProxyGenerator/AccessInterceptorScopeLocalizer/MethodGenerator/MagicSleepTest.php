<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep}
 *
 * @group Coverage
 */
final class MagicSleepTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicSleep(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__sleep', $magicGet->getName());
        self::assertEmpty($magicGet->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = array_keys((array) $this);%a', $magicGet->getBody());
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep::__construct
     */
    public function testBodyStructureWithInheritedMethod(): void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicGet = new MagicSleep(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__sleep', $magicGet->getName());
        self::assertEmpty($magicGet->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & parent::__sleep();%a', $magicGet->getBody());
    }
}
