<?php

declare(strict_types=1);

namespace ProxyManagerLts\Proxy;

/**
 * Value holder marker
 *
 * @psalm-template WrappedValueHolderType of object
 */
interface ValueHolderInterface extends ProxyInterface
{
    /**
     * @return object|null the wrapped value
     *
     * @psalm-return WrappedValueHolderType|null
     */
    public function getWrappedValueHolderValue();
}
