<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker
 * @group Coverage
 */
final class InitializationTrackerTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializationTracker();
    }

    public function testInitializationFlagIsFalseByDefault(): void
    {
        $property = $this->createProperty();

        self::assertFalse($property->getDefaultValue()->getValue());
    }
}
