<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\NullObject\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use ProxyManagerLtsTestAsset\ClassWithMixedProperties;
use ProxyManagerLtsTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerLtsTestAsset\ClassWithPrivateProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
final class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure(): void
    {
        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertSame(ClassWithMixedProperties::class, (string) $constructor->getReturnType());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->publicProperty0 = null;
$instance->publicProperty1 = null;
$instance->publicProperty2 = null;

return $instance;',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties(): void
    {
        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithPrivateProperties::class)
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(ClassWithPrivateProperties::class, (string) $constructor->getReturnType());
        $body = $constructor->getBody();
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

return $instance;',
            $body
        );
    }

    /**
     * @requires PHP 7.4
     */
    public function testBodyStructureWithTypedProperties(): void
    {
        $constructor = new StaticProxyConstructor(new ReflectionClass(ClassWithMixedTypedProperties::class));

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertSame(ClassWithMixedTypedProperties::class, (string) $constructor->getReturnType());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->publicUnTypedProperty = null;
$instance->publicUnTypedPropertyWithoutDefaultValue = null;
$instance->publicNullableBoolProperty = null;
$instance->publicNullableBoolPropertyWithoutDefaultValue = null;
$instance->publicNullableIntProperty = null;
$instance->publicNullableIntPropertyWithoutDefaultValue = null;
$instance->publicNullableFloatProperty = null;
$instance->publicNullableFloatPropertyWithoutDefaultValue = null;
$instance->publicNullableStringProperty = null;
$instance->publicNullableStringPropertyWithoutDefaultValue = null;
$instance->publicNullableArrayProperty = null;
$instance->publicNullableArrayPropertyWithoutDefaultValue = null;
$instance->publicNullableIterableProperty = null;
$instance->publicNullableIterablePropertyWithoutDefaultValue = null;
$instance->publicNullableObjectProperty = null;
$instance->publicNullableClassProperty = null;

return $instance;',
            $constructor->getBody()
        );
    }
}
