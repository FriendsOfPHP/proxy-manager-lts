<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\GeneratorStrategy;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;

use function class_exists;
use function ini_get;
use function strpos;
use function uniqid;

/**
 * Tests for {@see \ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy}
 *
 * @group Coverage
 */
final class EvaluatingGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerate(): void
    {
        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }

    /**
     * @covers \ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerateWithDisabledEval(): void
    {
        if (! ini_get('suhosin.executor.disable_eval')) {
            self::markTestSkipped('Ini setting "suhosin.executor.disable_eval" is needed to run this test');
        }

        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = 'Foo' . uniqid();
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }
}
