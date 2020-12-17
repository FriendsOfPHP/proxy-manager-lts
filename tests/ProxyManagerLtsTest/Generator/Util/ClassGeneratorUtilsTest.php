<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Generator\Util;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\Util\ClassGeneratorUtils;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\ClassWithFinalMethods;
use ReflectionClass;

/**
 * Test to {@see ProxyManagerLts\Generator\Util\ClassGeneratorUtils}
 *
 * @covers ProxyManagerLts\Generator\Util\ClassGeneratorUtils
 * @group Coverage
 */
final class ClassGeneratorUtilsTest extends TestCase
{
    public function testCantAddAFinalMethod(): void
    {
        $classGenerator  = $this->createMock(ClassGenerator::class);
        $methodGenerator = $this->createMock(MethodGenerator::class);

        $methodGenerator
            ->expects(self::once())
            ->method('getName')
            ->willReturn('foo');

        $classGenerator
            ->expects(self::never())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass(ClassWithFinalMethods::class);

        self::assertFalse(ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator));
    }

    public function testCanAddANotFinalMethod(): void
    {
        $classGenerator  = $this->createMock(ClassGenerator::class);
        $methodGenerator = $this->createMock(MethodGenerator::class);

        $methodGenerator
            ->expects(self::once())
            ->method('getName')
            ->willReturn('publicMethod');

        $classGenerator
            ->expects(self::once())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass(BaseClass::class);

        self::assertTrue(ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator));
    }
}
