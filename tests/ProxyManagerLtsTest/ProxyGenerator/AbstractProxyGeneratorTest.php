<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerLtsTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerLtsTestAsset\IterableMethodTypeHintedInterface;
use ProxyManagerLtsTestAsset\ObjectMethodTypeHintedInterface;
use ProxyManagerLtsTestAsset\ReturnTypeHintedClass;
use ProxyManagerLtsTestAsset\ReturnTypeHintedInterface;
use ProxyManagerLtsTestAsset\VoidMethodTypeHintedClass;
use ProxyManagerLtsTestAsset\VoidMethodTypeHintedInterface;
use ReflectionClass;

/**
 * Base test for proxy generators
 *
 * @group Coverage
 */
abstract class AbstractProxyGeneratorTest extends TestCase
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     * @psalm-param class-string $className
     */
    public function testGeneratesValidCode(string $className): void
    {
        if (false !== strpos($className, 'TypedProp') && \PHP_VERSION_ID < 70400) {
            self::markTestSkipped('PHP 7.4 required.');
        }

        $generator          = $this->getProxyGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('AbstractProxyGeneratorTest');
        $generatedClass     = new ClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            self::assertTrue($generatedReflection->implementsInterface($className));
        } else {
            $parentClass = $generatedReflection->getParentClass();

            self::assertInstanceOf(ReflectionClass::class, $parentClass);
            self::assertSame($originalClass->getName(), $parentClass->getName());
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * Retrieve a new generator instance
     */
    abstract protected function getProxyGenerator(): ProxyGeneratorInterface;

    /**
     * Retrieve interfaces that should be implemented by the generated code
     *
     * @return string[]
     *
     * @psalm-return list<class-string>
     */
    abstract protected function getExpectedImplementedInterfaces(): array;

    /** @return string[][] */
    public function getTestedImplementations(): array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithMixedTypedProperties::class],
            [ClassWithMixedReferenceableTypedProperties::class],
            [ClassWithAbstractPublicMethod::class],
            [BaseInterface::class],
            [ReturnTypeHintedClass::class],
            [VoidMethodTypeHintedClass::class],
            [ReturnTypeHintedInterface::class],
            [VoidMethodTypeHintedInterface::class],
            [IterableMethodTypeHintedInterface::class],
            [ObjectMethodTypeHintedInterface::class],
        ];
    }
}
