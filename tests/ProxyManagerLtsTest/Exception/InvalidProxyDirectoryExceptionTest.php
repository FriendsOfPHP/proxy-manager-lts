<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\InvalidProxyDirectoryException;

/**
 * Tests for {@see \ProxyManagerLts\Exception\InvalidProxyDirectoryException}
 *
 * @covers \ProxyManagerLts\Exception\InvalidProxyDirectoryException
 * @group Coverage
 */
final class InvalidProxyDirectoryExceptionTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\Exception\InvalidProxyDirectoryException::proxyDirectoryNotFound
     */
    public function testProxyDirectoryNotFound(): void
    {
        $exception = InvalidProxyDirectoryException::proxyDirectoryNotFound('foo/bar');

        self::assertSame('Provided directory "foo/bar" does not exist', $exception->getMessage());
    }
}
