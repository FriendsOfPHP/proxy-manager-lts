<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\MagicMethodGenerator;
use ReflectionClass;

use function var_export;

/**
 * Magic `__isset` method for remote objects
 */
class MagicIsset extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapterProperty)
    {
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $this->setDocBlock('@param string $name');
        $this->setBody(
            '$return = $this->' . $adapterProperty->getName() . '->call(' . var_export($originalClass->getName(), true)
            . ', \'__isset\', array($name));' . "\n\n"
            . 'return $return;'
        );
    }
}
