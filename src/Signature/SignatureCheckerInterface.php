<?php

declare(strict_types=1);

namespace ProxyManagerLts\Signature;

use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;
use ReflectionClass;

/**
 * Generator for signatures to be used to check the validity of generated code
 */
interface SignatureCheckerInterface
{
    /**
     * Checks whether the given signature is valid or not
     *
     * @param array<string, mixed> $parameters
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     */
    public function checkSignature(ReflectionClass $class, array $parameters): void;
}
