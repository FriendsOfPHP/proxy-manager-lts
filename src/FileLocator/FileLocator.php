<?php

declare(strict_types=1);

namespace ProxyManagerLts\FileLocator;

use ProxyManagerLts\Exception\InvalidProxyDirectoryException;

use function realpath;
use function str_replace;

use const DIRECTORY_SEPARATOR;

class FileLocator implements FileLocatorInterface
{
    protected $proxiesDirectory;

    /**
     * @throws InvalidProxyDirectoryException
     */
    public function __construct(string $proxiesDirectory)
    {
        $absolutePath = realpath($proxiesDirectory);

        if ($absolutePath === false) {
            throw InvalidProxyDirectoryException::proxyDirectoryNotFound($proxiesDirectory);
        }

        $this->proxiesDirectory = $absolutePath;
    }

    public function getProxyFileName(string $className): string
    {
        return $this->proxiesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
