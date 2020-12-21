--TEST--
Verifies that generated lazy loading ghost objects disallow reading non-existing properties via direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManagerLts\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

$proxy->nonExisting;
?>
--EXPECTF--
%SNotice: Undefined property: Kitchen::$nonExisting in %a