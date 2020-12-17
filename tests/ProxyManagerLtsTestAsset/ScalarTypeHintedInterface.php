<?php

declare(strict_types=1);

namespace ProxyManagerLtsTestAsset;

interface ScalarTypeHintedInterface
{
    public function acceptString(string $param);

    public function acceptInteger(int $param);

    public function acceptBoolean(bool $param);

    public function acceptFloat(float $param);
}
