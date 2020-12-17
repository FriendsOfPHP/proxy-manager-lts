<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\Signature\Exception\MissingSignatureException}
 *
 * @covers \ProxyManagerLts\Signature\Exception\MissingSignatureException
 * @group Coverage
 */
final class MissingSignatureExceptionTest extends TestCase
{
    public function testFromMissingSignature(): void
    {
        $exception = MissingSignatureException::fromMissingSignature(
            new ReflectionClass(self::class),
            ['foo' => 'bar', 'baz' => 'tab'],
            'expected-signature'
        );

        self::assertSame(
            'No signature found for class "'
            . self::class
            . '", expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
