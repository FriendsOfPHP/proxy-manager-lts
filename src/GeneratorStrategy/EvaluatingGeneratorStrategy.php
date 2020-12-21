<?php

declare(strict_types=1);

namespace ProxyManagerLts\GeneratorStrategy;

use Laminas\Code\Generator\ClassGenerator;
use Symfony\Component\Filesystem\Filesystem;

use function assert;
use function ini_get;
use function is_string;
use function unlink;

/**
 * Generator strategy that produces the code and evaluates it at runtime
 */
class EvaluatingGeneratorStrategy implements GeneratorStrategyInterface
{
    /** @var bool flag indicating whether {@see eval} can be used */
    private $canEval = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        $this->canEval = ! ini_get('suhosin.executor.disable_eval');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Evaluates the generated code before returning it
     *
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator): string
    {
        $code = $classGenerator->generate();
        assert(is_string($code));

        // @codeCoverageIgnoreStart
        if (! $this->canEval) {
            $fileName = __DIR__ . '/EvaluatingGeneratorStrategy.php.tmp';
            (new Filesystem())->dumpFile($fileName, "<?php\n" . $code);

            /* @noinspection PhpIncludeInspection */
            require $fileName;
            unlink($fileName);

            return $code;
        }

        // @codeCoverageIgnoreEnd

        eval($code);

        return $code;
    }
}
