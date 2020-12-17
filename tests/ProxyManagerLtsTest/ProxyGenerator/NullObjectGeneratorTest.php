<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\Proxy\NullObjectInterface;
use ProxyManagerLts\ProxyGenerator\NullObjectGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ReflectionClass;
use ReflectionMethod;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\NullObjectGenerator}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\NullObjectGenerator
 * @group Coverage
 */
final class NullObjectGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     * @psalm-param class-string $className
     * @psalm-suppress MoreSpecificImplementedParamType
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
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }

        /**
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress MixedAssignment
         * @psalm-suppress MixedMethodCall
         */
        $proxy = $generatedClassName::staticProxyConstructor();

        self::assertInstanceOf($className, $proxy);

        foreach (
            Properties::fromReflectionClass($generatedReflection)
                ->onlyNullableProperties()
                ->getPublicProperties() as $property
        ) {
            /** @psalm-suppress MixedPropertyFetch */
            self::assertNull($proxy->{$property->getName()});
        }

        foreach ($generatedReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() || $method->isStatic()) {
                continue;
            }

            $callback = [$proxy, $method->getName()];

            self::assertIsCallable($callback);
            self::assertNull($callback());
        }
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new NullObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            NullObjectInterface::class,
        ];
    }

    /**
     * @psalm-return array<int, array<int, class-string>>
     */
    public function getTestedImplementations(): array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithMixedTypedProperties::class],
            [ClassWithMixedReferenceableTypedProperties::class],
            [BaseInterface::class],
        ];
    }
}
