<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Generator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;

use function strlen;

/**
 * Tests for {@see \ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator}
 *
 * @group Coverage
 * @covers \ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator
 */
final class UniqueIdentifierGeneratorTest extends TestCase
{
    /**
     * @dataProvider getBaseIdentifierNames
     */
    public function testGeneratesUniqueIdentifiers(string $name): void
    {
        self::assertNotSame(
            UniqueIdentifierGenerator::getIdentifier($name),
            UniqueIdentifierGenerator::getIdentifier($name)
        );
    }

    /**
     * @dataProvider getBaseIdentifierNames
     */
    public function testGeneratesValidIdentifiers(string $name): void
    {
        self::assertMatchesRegularExpression(
            '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/',
            UniqueIdentifierGenerator::getIdentifier($name)
        );
    }

    /**
     * @dataProvider getBaseIdentifierNames
     */
    public function testGeneratedIdentifierEntropy(string $name): void
    {
        self::assertGreaterThan(14, strlen(UniqueIdentifierGenerator::getIdentifier($name)));
    }

    /**
     * Data provider generating identifier names to be checked
     *
     * @return string[][]
     */
    public static function getBaseIdentifierNames(): array
    {
        return [
            [''],
            ['1'],
            ['foo'],
            ['Foo'],
            ['bar'],
            ['Bar'],
            ['foo_bar'],
        ];
    }
}
