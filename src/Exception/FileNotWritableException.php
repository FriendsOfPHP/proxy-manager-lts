<?php

declare(strict_types=1);

namespace ProxyManagerLts\Exception;

use Symfony\Component\Filesystem\Exception\IOException;
use UnexpectedValueException;

use function sprintf;

/**
 * Exception for non writable files
 */
class FileNotWritableException extends UnexpectedValueException implements ExceptionInterface
{
    public static function fromPrevious(\Throwable $previous): self
    {
        return new self($previous->getMessage(), 0, $previous);
    }
}
