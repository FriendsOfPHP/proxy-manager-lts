<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
final class MagicIssetTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $adapter    = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('foo');

        $magicGet = new MagicIsset($reflection, $adapter);

        self::assertSame('__isset', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'ProxyManagerLtsTestAsset\\\EmptyClass\', \'__isset\', array($name));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
