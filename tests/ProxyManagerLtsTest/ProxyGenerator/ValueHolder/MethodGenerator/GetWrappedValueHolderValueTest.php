<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue}
 *
 * @group Coverage
 */
final class GetWrappedValueHolderValueTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue::__construct
     */
    public function testBodyStructure(): void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $getter = new GetWrappedValueHolderValue($valueHolder);

        self::assertSame('getWrappedValueHolderValue', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
    }
}
