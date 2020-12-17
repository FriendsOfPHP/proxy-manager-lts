<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithVariadicConstructorArgument;
use ProxyManagerLtsTestAsset\EmptyClass;
use ProxyManagerLtsTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\Constructor}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\ValueHolder\MethodGenerator\Constructor
 * @group Coverage
 */
final class ConstructorTest extends TestCase
{
    public function testBodyStructure(): void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $constructor = Constructor::generateMethod(
            new ReflectionClass(
                ClassWithTwoPublicProperties::class
            ),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?? new \ReflectionClass(\'ProxyManagerLtsTestAsset\\\\ProxyGenerator\\\\LazyLoading\\\\'
            . 'MethodGenerator\\\\ClassWithTwoPublicProperties\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
unset($this->bar, $this->baz);

}',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties(): void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $constructor = Constructor::generateMethod(
            new ReflectionClass(EmptyClass::class),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?? new \ReflectionClass(\'ProxyManagerLtsTestAsset\\\\EmptyClass\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
}',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithStaticProperties(): void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $constructor = Constructor::generateMethod(new ReflectionClass(ClassWithMixedProperties::class), $valueHolder);

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());

        $expectedCode = 'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?? new \ReflectionClass(\'ProxyManagerLtsTestAsset\\\\ClassWithMixedProperties\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
unset($this->publicProperty0, $this->publicProperty1, $this->publicProperty2, $this->protectedProperty0, '
            . '$this->protectedProperty1, $this->protectedProperty2);

\Closure::bind(function (\ProxyManagerLtsTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $this, \'ProxyManagerLtsTestAsset\\\\ClassWithMixedProperties\')->__invoke($this);

}';

        self::assertSame($expectedCode, $constructor->getBody());
    }

    public function testBodyStructureWithVariadicArguments(): void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $constructor = Constructor::generateMethod(
            new ReflectionClass(ClassWithVariadicConstructorArgument::class),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(2, $constructor->getParameters());

        $expectedCode = <<<'PHP'
static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?? new \ReflectionClass('ProxyManagerLtsTestAsset\\ClassWithVariadicConstructorArgument');
    $this->foo = $reflection->newInstanceWithoutConstructor();
\Closure::bind(function (\ProxyManagerLtsTestAsset\ClassWithVariadicConstructorArgument $instance) {
    unset($instance->foo, $instance->bar);
}, $this, 'ProxyManagerLtsTestAsset\\ClassWithVariadicConstructorArgument')->__invoke($this);

}

$this->foo->__construct($foo, ...$bar);
PHP;

        self::assertSame($expectedCode, $constructor->getBody());
    }
}
