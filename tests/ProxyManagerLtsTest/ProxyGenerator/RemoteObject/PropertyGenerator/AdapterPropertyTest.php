<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\RemoteObject\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty
 * @group Coverage
 */
final class AdapterPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new AdapterProperty();
    }
}
