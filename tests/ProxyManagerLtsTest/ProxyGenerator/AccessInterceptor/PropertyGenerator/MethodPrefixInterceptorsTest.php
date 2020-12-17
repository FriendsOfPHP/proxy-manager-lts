<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors
 * @group Coverage
 */
final class MethodPrefixInterceptorsTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new MethodPrefixInterceptors();
    }
}
