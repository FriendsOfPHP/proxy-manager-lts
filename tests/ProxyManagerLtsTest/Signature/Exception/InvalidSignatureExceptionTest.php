<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\Signature\Exception\InvalidSignatureException}
 *
 * @covers \ProxyManagerLts\Signature\Exception\InvalidSignatureException
 * @group Coverage
 */
final class InvalidSignatureExceptionTest extends TestCase
{
    public function testFromInvalidSignature(): void
    {
        $exception = InvalidSignatureException::fromInvalidSignature(
            new ReflectionClass(self::class),
            ['foo' => 'bar', 'baz' => 'tab'],
            'blah',
            'expected-signature'
        );

        self::assertSame(
            'Found signature "blah" for class "'
            . self::class
            . '" does not correspond to expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
