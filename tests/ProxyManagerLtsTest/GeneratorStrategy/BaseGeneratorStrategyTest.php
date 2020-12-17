<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\GeneratorStrategy;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\BaseGeneratorStrategy;

use function strpos;

/**
 * Tests for {@see \ProxyManagerLts\GeneratorStrategy\BaseGeneratorStrategy}
 *
 * @group Coverage
 */
final class BaseGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\GeneratorStrategy\BaseGeneratorStrategy::generate
     */
    public function testGenerate(): void
    {
        $strategy       = new BaseGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
    }
}
