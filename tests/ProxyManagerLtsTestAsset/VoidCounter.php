<?php

declare(strict_types=1);

namespace ProxyManagerLtsTestAsset;

class VoidCounter
{
    /**
     * @var int
     */
    public $counter = 0;

    public function increment(int $amount) : void
    {
        $this->counter += $amount;
    }
}
