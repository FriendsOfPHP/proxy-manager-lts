--TEST--
Verifies that lazy loading value holder factory can generate proxy for PHP core classes.
?>
--SKIPIF--
<?php

require_once __DIR__ . '/init.php';

try {
    if (PHP_VERSION_ID >= 80000) {
        Laminas\Code\Generator\TypeGenerator::fromTypeString('array|string');
    }
} catch (\InvalidArgumentException $e) {
    die('skip laminas/laminas-code >= 3.5 must be installed');
}
--FILE--
<?php

require_once __DIR__ . '/init.php';

class PharMock extends Phar
{
    public function __construct()
    {
    }

    public function compress($compression_type, $file_ext = null): ?Phar
    {
        echo $compression_type;

        return null;
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$factory
    ->createProxy(Phar::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
        $initializer = null;
        $wrapped     = new PharMock();
    })
    ->compress(123);

?>
--EXPECT--
123
