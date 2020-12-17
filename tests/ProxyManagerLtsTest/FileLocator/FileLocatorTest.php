<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\FileLocator;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\InvalidProxyDirectoryException;
use ProxyManagerLts\FileLocator\FileLocator;

use const DIRECTORY_SEPARATOR;

/**
 * Tests for {@see \ProxyManagerLts\FileLocator\FileLocator}
 *
 * @group Coverage
 */
final class FileLocatorTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\FileLocator\FileLocator::__construct
     * @covers \ProxyManagerLts\FileLocator\FileLocator::getProxyFileName
     */
    public function testGetProxyFileName(): void
    {
        $locator = new FileLocator(__DIR__);

        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'FooBarBaz.php', $locator->getProxyFileName('Foo\\Bar\\Baz'));
        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'Foo_Bar_Baz.php', $locator->getProxyFileName('Foo_Bar_Baz'));
    }

    /**
     * @covers \ProxyManagerLts\FileLocator\FileLocator::__construct
     */
    public function testRejectsNonExistingDirectory(): void
    {
        $this->expectException(InvalidProxyDirectoryException::class);
        new FileLocator(__DIR__ . '/non-existing');
    }
}
