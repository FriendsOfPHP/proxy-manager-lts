<?php

declare(strict_types=1);

namespace ProxyManagerLtsTestAsset;

interface IterableMethodTypeHintedInterface
{
    public function returnIterable() : iterable;
}
