<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\Proxy\RemoteObjectInterface;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\ProxyGenerator\RemoteObjectGenerator;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ReflectionClass;

use function array_diff;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\RemoteObjectGenerator}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\RemoteObjectGenerator
 * @group Coverage
 */
final class RemoteObjectGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
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
            self::assertEmpty(
                array_diff($originalClass->getInterfaceNames(), $generatedReflection->getInterfaceNames())
            );
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new RemoteObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            RemoteObjectInterface::class,
        ];
    }

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
            [BaseInterface::class],
        ];
    }
}
