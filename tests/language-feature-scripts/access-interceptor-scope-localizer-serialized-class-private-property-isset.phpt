--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class private property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

set_error_handler(function () {}, E_DEPRECATED);

class Kitchen implements \Serializable
{
    private $sweets = 'candy';

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

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)
