<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use ProxyManagerLts\Proxy\AccessInterceptorInterface;
use ProxyManagerLts\Proxy\AccessInterceptorValueHolderInterface;
use ProxyManagerLts\Proxy\ValueHolderInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolderGenerator
 * @group Coverage
 */
final class AccessInterceptorValueHolderTest extends AbstractProxyGeneratorTest
{
    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            AccessInterceptorValueHolderInterface::class,
            AccessInterceptorInterface::class,
            ValueHolderInterface::class,
        ];
    }
}
