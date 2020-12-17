<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use ProxyManagerLts\Proxy\VirtualProxyInterface;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolderGenerator}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolderGenerator
 * @group Coverage
 */
final class LazyLoadingValueHolderGeneratorTest extends AbstractProxyGeneratorTest
{
    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new LazyLoadingValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [VirtualProxyInterface::class];
    }
}
