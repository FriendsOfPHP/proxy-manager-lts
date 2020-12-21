<?php

declare(strict_types=1);

namespace ProxyManagerLts\Autoloader;

use ProxyManagerLts\FileLocator\FileLocatorInterface;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;

use function class_exists;
use function file_exists;

class Autoloader implements AutoloaderInterface
{
    protected $fileLocator;
    protected $classNameInflector;

    public function __construct(FileLocatorInterface $fileLocator, ClassNameInflectorInterface $classNameInflector)
    {
        $this->fileLocator        = $fileLocator;
        $this->classNameInflector = $classNameInflector;
    }

    public function __invoke(string $className): bool
    {
        if (class_exists($className, false) || ! $this->classNameInflector->isProxyClassName($className)) {
            return false;
        }

        $file = $this->fileLocator->getProxyFileName($className);

        if (! file_exists($file)) {
            return false;
        }

        /* @noinspection PhpIncludeInspection */
        /* @noinspection UsingInclusionOnceReturnValueInspection */
        return (bool) require_once $file;
    }
}
