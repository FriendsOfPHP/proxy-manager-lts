<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\UnsupportedProxiedClassException;
use ProxyManagerLts\ProxyGenerator\Util\Properties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithPrivateProperties;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManagerLts\Exception\UnsupportedProxiedClassException}
 *
 * @covers \ProxyManagerLts\Exception\UnsupportedProxiedClassException
 * @group Coverage
 */
final class UnsupportedProxiedClassExceptionTest extends TestCase
{
    public function testUnsupportedLocalizedReflectionProperty(): void
    {
        self::assertSame(
            'Provided reflection property "property0" of class "' . ClassWithPrivateProperties::class
            . '" is private and cannot be localized in PHP 5.3',
            UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty(
                new ReflectionProperty(ClassWithPrivateProperties::class, 'property0')
            )->getMessage()
        );
    }

    /**
     * @requires PHP 7.4
     */
    public function testNonReferenceableLocalizedReflectionProperties(): void
    {
        $reflectionClass = new ReflectionClass(ClassWithMixedTypedProperties::class);

        self::assertSame(
            'Cannot create references for following properties of class '
            . ClassWithMixedTypedProperties::class
            . ': publicBoolPropertyWithoutDefaultValue, publicNullableBoolPropertyWithoutDefaultValue, '
            . 'publicIntPropertyWithoutDefaultValue, publicNullableIntPropertyWithoutDefaultValue, '
            . 'publicFloatPropertyWithoutDefaultValue, publicNullableFloatPropertyWithoutDefaultValue, '
            . 'publicStringPropertyWithoutDefaultValue, publicNullableStringPropertyWithoutDefaultValue, '
            . 'publicArrayPropertyWithoutDefaultValue, publicNullableArrayPropertyWithoutDefaultValue, '
            . 'publicIterablePropertyWithoutDefaultValue, publicNullableIterablePropertyWithoutDefaultValue, '
            . 'publicObjectProperty, publicNullableObjectProperty, publicClassProperty, publicNullableClassProperty, '
            . 'protectedBoolPropertyWithoutDefaultValue, protectedNullableBoolPropertyWithoutDefaultValue, '
            . 'protectedIntPropertyWithoutDefaultValue, protectedNullableIntPropertyWithoutDefaultValue, '
            . 'protectedFloatPropertyWithoutDefaultValue, protectedNullableFloatPropertyWithoutDefaultValue, '
            . 'protectedStringPropertyWithoutDefaultValue, protectedNullableStringPropertyWithoutDefaultValue, '
            . 'protectedArrayPropertyWithoutDefaultValue, protectedNullableArrayPropertyWithoutDefaultValue, '
            . 'protectedIterablePropertyWithoutDefaultValue, protectedNullableIterablePropertyWithoutDefaultValue, '
            . 'protectedObjectProperty, protectedNullableObjectProperty, protectedClassProperty, '
            . 'protectedNullableClassProperty, privateBoolPropertyWithoutDefaultValue, '
            . 'privateNullableBoolPropertyWithoutDefaultValue, privateIntPropertyWithoutDefaultValue, '
            . 'privateNullableIntPropertyWithoutDefaultValue, privateFloatPropertyWithoutDefaultValue, '
            . 'privateNullableFloatPropertyWithoutDefaultValue, privateStringPropertyWithoutDefaultValue, '
            . 'privateNullableStringPropertyWithoutDefaultValue, privateArrayPropertyWithoutDefaultValue, '
            . 'privateNullableArrayPropertyWithoutDefaultValue, privateIterablePropertyWithoutDefaultValue, '
            . 'privateNullableIterablePropertyWithoutDefaultValue, privateObjectProperty, '
            . 'privateNullableObjectProperty, privateClassProperty, privateNullableClassProperty',
            UnsupportedProxiedClassException::nonReferenceableLocalizedReflectionProperties(
                $reflectionClass,
                Properties::fromReflectionClass($reflectionClass)
                    ->onlyNonReferenceableProperties()
                    ->onlyInstanceProperties()
            )->getMessage()
        );
    }
}
