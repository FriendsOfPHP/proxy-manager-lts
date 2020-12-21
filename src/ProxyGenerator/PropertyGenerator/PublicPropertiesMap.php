<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\PropertyGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\Generator\Util\IdentifierSuffixer;
use ProxyManagerLts\ProxyGenerator\Util\Properties;

/**
 * Map of public properties that exist in the class being proxied
 */
class PublicPropertiesMap extends PropertyGenerator
{
    /** @var array<string, bool> */
    private $publicProperties = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Properties $properties)
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('publicProperties'));

        foreach ($properties->getPublicProperties() as $publicProperty) {
            $this->publicProperties[$publicProperty->getName()] = true;
        }

        $this->setDefaultValue($this->publicProperties);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setStatic(true);
        $this->setDocBlock('@var bool[] map of public properties of the parent class');
    }

    public function isEmpty(): bool
    {
        return ! $this->publicProperties;
    }
}
