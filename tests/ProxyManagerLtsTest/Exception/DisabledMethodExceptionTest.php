<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\DisabledMethodException;

/**
 * Tests for {@see \ProxyManagerLts\Exception\DisabledMethodException}
 *
 * @covers \ProxyManagerLts\Exception\DisabledMethodException
 * @group Coverage
 */
final class DisabledMethodExceptionTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\Exception\DisabledMethodException::disabledMethod
     */
    public function testProxyDirectoryNotFound(): void
    {
        $exception = DisabledMethodException::disabledMethod('foo::bar');

        self::assertSame('Method "foo::bar" is forcefully disabled', $exception->getMessage());
    }
}
