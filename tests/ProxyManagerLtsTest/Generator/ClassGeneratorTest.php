<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Generator;

use Countable;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Generator\ClassGenerator;
use stdClass;

/**
 * Tests for {@see \ProxyManagerLts\Generator\ClassGenerator}
 *
 * @group Coverage
 */
final class ClassGeneratorTest extends TestCase
{
    /**
     * @covers \ProxyManagerLts\Generator\ClassGenerator::setExtendedClass
     */
    public function testExtendedClassesAreFQCNs(): void
    {
        $desiredFqcn     = '\\stdClass';
        $classNameInputs = [stdClass::class, '\\stdClass\\'];

        foreach ($classNameInputs as $className) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setExtendedClass($className);

            self::assertEquals($desiredFqcn, $classGenerator->getExtendedClass());
        }
    }

    /**
     * @covers \ProxyManagerLts\Generator\ClassGenerator::setImplementedInterfaces
     */
    public function testImplementedInterfacesAreFQCNs(): void
    {
        $desiredFqcns        = ['\\Countable'];
        $interfaceNameInputs = [[Countable::class], ['\\Countable\\']];

        foreach ($interfaceNameInputs as $interfaceNames) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setImplementedInterfaces($interfaceNames);

            self::assertEquals($desiredFqcns, $classGenerator->getImplementedInterfaces());
        }
    }
}
