--TEST--
Verifies that generated null object disallow public function
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    public $foo = 'bar';
}

$factory = new \ProxyManagerLts\Factory\NullObjectFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class);

var_dump($proxy->foo);
?>
--EXPECT--
NULL
