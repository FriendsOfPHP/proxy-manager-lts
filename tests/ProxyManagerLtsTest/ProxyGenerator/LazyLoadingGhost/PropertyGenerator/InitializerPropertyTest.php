<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty
 * @group Coverage
 */
final class InitializerPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializerProperty();
    }
}
