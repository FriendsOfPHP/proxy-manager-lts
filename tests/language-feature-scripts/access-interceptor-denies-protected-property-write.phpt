--TEST--
Verifies that generated access interceptors disallow protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManagerLts\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot %s property%sin %a