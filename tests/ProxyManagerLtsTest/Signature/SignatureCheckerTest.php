<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Signature;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;
use ProxyManagerLts\Signature\SignatureChecker;
use ProxyManagerLts\Signature\SignatureGeneratorInterface;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\Signature\SignatureChecker}
 *
 * @covers \ProxyManagerLts\Signature\SignatureChecker
 * @group Coverage
 */
final class SignatureCheckerTest extends TestCase
{
    public $signatureExample = 'valid-signature';
    private $signatureChecker;

    /** @var SignatureGeneratorInterface&MockObject */
    private $signatureGenerator;

    protected function setUp(): void
    {
        $this->signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);
        $this->signatureChecker   = new SignatureChecker($this->signatureGenerator);
    }

    public function testCheckSignatureWithValidKey(): void
    {
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('Example');
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidKey(): void
    {
        $this
            ->signatureGenerator

            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('InvalidKey');
        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this->expectException(MissingSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidValue(): void
    {
        $this
            ->signatureGenerator
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('Example');
        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('invalid-signature');

        $this->expectException(InvalidSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }
}
