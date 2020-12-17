<?php

declare(strict_types=1);

namespace ProxyManagerLtsTestAsset;

/**
 * Base test class to verify interactions with public typed properties
 * that are nullable.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithPublicStringNullableTypedProperty
{
    public ?string $typedNullableProperty;
}
