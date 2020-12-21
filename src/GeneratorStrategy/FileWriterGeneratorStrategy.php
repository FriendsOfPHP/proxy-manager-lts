<?php

declare(strict_types=1);

namespace ProxyManagerLts\GeneratorStrategy;

use Closure;
use Laminas\Code\Generator\ClassGenerator;
use ProxyManagerLts\Exception\FileNotWritableException;
use ProxyManagerLts\FileLocator\FileLocatorInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use function assert;
use function is_string;
use function restore_error_handler;
use function set_error_handler;

/**
 * Generator strategy that writes the generated classes to disk while generating them
 *
 * {@inheritDoc}
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    protected $fileLocator;
    private $emptyErrorHandler;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator  = $fileLocator;
        $this->emptyErrorHandler = static function (int $type, string $message, string $file, int $line) {
            if (error_reporting() & $type) {
                throw new \ErrorException($message, 0, $type, $file, $line);
            }
        };
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     *
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator): string
    {
        $generatedCode = $classGenerator->generate();
        assert(is_string($generatedCode));
        $className = $classGenerator->getNamespaceName() . '\\' . $classGenerator->getName();
        $fileName  = $this->fileLocator->getProxyFileName($className);

        set_error_handler($this->emptyErrorHandler);

        try {
            (new Filesystem())->dumpFile($fileName, "<?php\n\n" . $generatedCode);

            return $generatedCode;
        } catch (IOException $e) {
            throw FileNotWritableException::fromPrevious($e);
        } finally {
            restore_error_handler();
        }
    }
}
