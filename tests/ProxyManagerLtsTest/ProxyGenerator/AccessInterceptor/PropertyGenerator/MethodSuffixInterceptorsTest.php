<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors
 * @group Coverage
 */
final class MethodSuffixInterceptorsTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new MethodSuffixInterceptors();
    }
}
