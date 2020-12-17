<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
final class MagicIssetTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicIsset = new MagicIsset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = $accessor();%a', $magicIsset->getBody());
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructureWithInheritedMethod(): void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $magicIsset = new MagicIsset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & parent::__isset($name);%a', $magicIsset->getBody());
    }
}
