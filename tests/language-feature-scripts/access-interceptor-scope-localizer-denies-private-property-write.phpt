--TEST--
Verifies that generated access interceptors disallow private property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManagerLts\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot %s property%sin %a