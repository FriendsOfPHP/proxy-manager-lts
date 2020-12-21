<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Signature;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Signature\ClassSignatureGenerator;
use ProxyManagerLts\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \ProxyManagerLts\Signature\ClassSignatureGenerator}
 *
 * @covers \ProxyManagerLts\Signature\ClassSignatureGenerator
 * @group Coverage
 */
final class ClassSignatureGeneratorTest extends TestCase
{
    /** @var SignatureGeneratorInterface&MockObject */
    private $signatureGenerator;
    private $classSignatureGenerator;

    protected function setUp(): void
    {
        $this->signatureGenerator      = $this->createMock(SignatureGeneratorInterface::class);
        $this->classSignatureGenerator = new ClassSignatureGenerator($this->signatureGenerator);
    }

    public function testAddSignature(): void
    {
        $classGenerator = $this->createMock(ClassGenerator::class);

        $classGenerator
            ->expects(self::once())
            ->method('addPropertyFromGenerator')
            ->with(self::callback(static function (PropertyGenerator $property): bool {
                return $property->getName() === 'signaturePropertyName'
                    && $property->isStatic()
                    && $property->getVisibility() === 'private'
                    && $property->getDefaultValue()->getValue() === 'valid-signature';
            }));

        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this
            ->signatureGenerator
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('PropertyName');

        $this->classSignatureGenerator->addSignature($classGenerator, ['foo' => 'bar']);
    }
}
