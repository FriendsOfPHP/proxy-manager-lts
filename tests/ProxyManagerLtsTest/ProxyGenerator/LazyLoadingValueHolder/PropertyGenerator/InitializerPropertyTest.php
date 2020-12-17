<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty
 * @group Coverage
 */
final class InitializerPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializerProperty();
    }
}
