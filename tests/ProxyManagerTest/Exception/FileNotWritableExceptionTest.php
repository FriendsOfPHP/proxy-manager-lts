<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Tests for {@see \ProxyManager\Exception\FileNotWritableException}
 *
 * @covers \ProxyManager\Exception\FileNotWritableException
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
