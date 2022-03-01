--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class protected property direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

set_error_handler(function () {}, E_DEPRECATED);

class Kitchen implements \Serializable
{
    protected $sweets = 'candy';

    #[\ReturnTypeWillChange]
    function serialize()
    {
        return $this->sweets;
    }

    #[\ReturnTypeWillChange]
    function unserialize($serialized)
    {
        $this->sweets = $serialized;
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets;
?>
--EXPECTF--
%SFatal error:%sCannot access protected property %s::$sweets in %a
