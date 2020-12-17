<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ProxyManagerLtsTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap
 * @group Coverage
 */
final class ProtectedPropertiesMapTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );
    }

    public function testExtractsProtectedProperties(): void
    {
        $map = new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );

        self::assertSame(
            [
                'protectedProperty0' => ClassWithMixedProperties::class,
                'protectedProperty1' => ClassWithMixedProperties::class,
                'protectedProperty2' => ClassWithMixedProperties::class,
            ],
            $map->getDefaultValue()->getValue()
        );
    }

    public function testSkipsAbstractProtectedMethods(): void
    {
        $map = new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractProtectedMethod::class))
        );

        self::assertSame([], $map->getDefaultValue()->getValue());
    }

    public function testIsStaticPrivate(): void
    {
        $map = $this->createProperty();

        self::assertTrue($map->isStatic());
        self::assertSame(ProtectedPropertiesMap::VISIBILITY_PRIVATE, $map->getVisibility());
    }
}
