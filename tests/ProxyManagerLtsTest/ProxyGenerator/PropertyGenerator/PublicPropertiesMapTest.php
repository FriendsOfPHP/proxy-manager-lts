<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithPublicProperties;
use ProxyManagerLtsTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap
 * @group Coverage
 */
final class PublicPropertiesMapTest extends TestCase
{
    public function testEmptyClass(): void
    {
        $publicProperties = new PublicPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(EmptyClass::class))
        );

        self::assertIsArray($publicProperties->getDefaultValue()->getValue());
        self::assertEmpty($publicProperties->getDefaultValue()->getValue());
        self::assertTrue($publicProperties->isStatic());
        self::assertSame('private', $publicProperties->getVisibility());
        self::assertTrue($publicProperties->isEmpty());
    }

    public function testClassWithPublicProperties(): void
    {
        $publicProperties = new PublicPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithPublicProperties::class))
        );

        self::assertTrue($publicProperties->isStatic());
        self::assertSame('private', $publicProperties->getVisibility());
        self::assertFalse($publicProperties->isEmpty());
        self::assertSame(
            [
                'property0' => true,
                'property1' => true,
                'property2' => true,
                'property3' => true,
                'property4' => true,
                'property5' => true,
                'property6' => true,
                'property7' => true,
                'property8' => true,
                'property9' => true,
            ],
            $publicProperties->getDefaultValue()->getValue()
        );
    }

    public function testClassWithMixedProperties(): void
    {
        self::assertSame(
            [
                'publicProperty0' => true,
                'publicProperty1' => true,
                'publicProperty2' => true,
            ],
            (new PublicPropertiesMap(
                Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
            ))->getDefaultValue()->getValue()
        );
    }
}
