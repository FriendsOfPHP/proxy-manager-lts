--TEST--
Verifies that generated lazy loading ghost objects disallow protected property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets = 'candy';
}

$factory = new \ProxyManagerLts\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)