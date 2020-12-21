<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManagerLts\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerLtsTestAsset\BaseClass;
use ProxyManagerLtsTestAsset\ClassWithMagicMethods;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet}
 *
 * @group Coverage
 */
final class MagicGetTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private $initializer;

    /** @var MethodGenerator&MockObject */
    private $initMethod;

    /** @var PublicPropertiesMap&MockObject */
    private $publicProperties;

    /** @var ProtectedPropertiesMap&MockObject */
    private $protectedProperties;

    /** @var PrivatePropertiesMap&MockObject */
    private $privateProperties;

    /** @var InitializationTracker&MockObject */
    private $initializationTracker;

    private $expectedCode = <<<'PHP'
$this->foo && ! $this->init && $this->baz('__get', array('name' => $name));

if (isset(self::$bar[$name])) {
    return $this->$name;
}

if (isset(self::$baz[$name])) {
    if ($this->init) {
        return $this->$name;
    }

    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$baz[$name];

    if ($object instanceof $expectedType) {
        return $this->$name;
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        return $this->$name;
    }
} elseif (isset(self::$tab[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$tab[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function & ($instance) use ($name) {
                return $instance->$name;
            }, null, $class);

        return $accessor($this);
    }

    if ($this->init || 'ReflectionProperty' === $class) {
        $tmpClass = key(self::$tab[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function & ($instance) use ($name) {
                return $instance->$name;
            }, null, $tmpClass);

        return $accessor($this);
    }
}

%a
PHP;

    protected function setUp(): void
    {
        $this->initializer           = $this->createMock(PropertyGenerator::class);
        $this->initMethod            = $this->createMock(MethodGenerator::class);
        $this->publicProperties      = $this->createMock(PublicPropertiesMap::class);
        $this->protectedProperties   = $this->createMock(ProtectedPropertiesMap::class);
        $this->privateProperties     = $this->createMock(PrivatePropertiesMap::class);
        $this->initializationTracker = $this->createMock(InitializationTracker::class);

        $this->initializer->method('getName')->willReturn('foo');
        $this->initMethod->method('getName')->willReturn('baz');
        $this->publicProperties->method('isEmpty')->willReturn(false);
        $this->publicProperties->method('getName')->willReturn('bar');
        $this->protectedProperties->method('getName')->willReturn('baz');
        $this->privateProperties->method('getName')->willReturn('tab');
        $this->initializationTracker->method('getName')->willReturn('init');
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet
     */
    public function testBodyStructure(): void
    {
        $magicGet = new MagicGet(
            new ReflectionClass(BaseClass::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties,
            $this->initializationTracker
        );

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat($this->expectedCode, $magicGet->getBody());
    }

    /**
     * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet
     */
    public function testBodyStructureWithOverriddenMagicGet(): void
    {
        $magicGet = new MagicGet(
            new ReflectionClass(ClassWithMagicMethods::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties,
            $this->initializationTracker
        );

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat($this->expectedCode, $magicGet->getBody());
        self::assertStringMatchesFormat('%Areturn parent::__get($name);', $magicGet->getBody());
    }
}
