<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
final class MagicIssetTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure(): void
    {
        $reflection       = new ReflectionClass(EmptyClass::class);
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);
        $publicProperties = $this->createMock(PublicPropertiesMap::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicIsset = new MagicIsset($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && (\$this->foo->__invoke(\$bar, \$this, '__isset', array('name' => \$name)"
            . ", \$this->foo) || 1) && \$this->bar = \$bar;\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return isset(\$this->bar->\$name);\n}"
            . '%areturn %s;',
            $magicIsset->getBody()
        );
    }
}
