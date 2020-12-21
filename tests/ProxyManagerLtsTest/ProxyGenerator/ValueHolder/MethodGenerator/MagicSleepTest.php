<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep}
 *
 * @group Coverage
 */
final class MagicSleepTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('bar');

        $magicSleep = new MagicSleep($reflection, $valueHolder);

        self::assertSame('__sleep', $magicSleep->getName());
        self::assertCount(0, $magicSleep->getParameters());
        self::assertSame(
            "return array('bar');",
            $magicSleep->getBody()
        );
    }
}
