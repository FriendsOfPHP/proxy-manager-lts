<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

class ClassWithNullDefaultMethodArguments
{
    public function acceptMixed(mixed $param = null)
    {
        return $param;
    }
}
