<?php

declare(strict_types=1);

namespace ProxyManagerLtsTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface FooServiceInterface
{
    /**
     * @return string
     */
    public function foo();
}
