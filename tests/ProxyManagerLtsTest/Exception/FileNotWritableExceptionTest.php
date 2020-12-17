<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\FileNotWritableException;

/**
 * Tests for {@see \ProxyManagerLts\Exception\FileNotWritableException}
 *
 * @covers \ProxyManagerLts\Exception\FileNotWritableException
 * @group Coverage
 */
final class FileNotWritableExceptionTest extends TestCase
{
    public function testFromPrevious(): void
    {
        $previousException = new \ErrorException('Previous exception message');

        $exception = FileNotWritableException::fromPrevious($previousException);

        self::assertSame('Previous exception message', $exception->getMessage());
        self::assertSame($previousException, $exception->getPrevious());
    }
}
