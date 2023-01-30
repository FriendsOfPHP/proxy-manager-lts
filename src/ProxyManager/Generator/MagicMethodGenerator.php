<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use LogicException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

use function get_class;
use function implode;
use function strtolower;

/**
 * Method generator for magic methods
 */
class MagicMethodGenerator extends MethodGenerator
{
    /**
     * @param ParameterGenerator[]|array[]|string[] $parameters
     */
    public function __construct(ReflectionClass $originalClass, string $name, array $parameters = [])
    {
        parent::__construct(
            $name,
            $parameters,
            self::FLAG_PUBLIC
        );

        $this->setReturnsReference(strtolower($name) === '__get');

        if (! $originalClass->hasMethod($name)) {
            return;
        }

        $originalMethod     = $originalClass->getMethod($name);
        $originalReturnType = $originalMethod->getReturnType();

        $this->setReturnsReference($originalMethod->returnsReference());

        if ($originalReturnType instanceof ReflectionNamedType) {
            $this->setReturnType(($originalReturnType->allowsNull() && $originalReturnType->getName() !== 'mixed' ? '?' : '') . $originalReturnType->getName());
        } elseif ($originalReturnType instanceof ReflectionUnionType || $originalReturnType instanceof ReflectionIntersectionType) {
            $returnType = [];
            foreach ($originalReturnType->getTypes() as $type) {
                $returnType[] = $type->getName();
            }

            $this->setReturnType(implode($originalReturnType instanceof ReflectionIntersectionType ? '&' : '|', $returnType));
        } elseif ($originalReturnType) {
            throw new LogicException('Unexpected ' . get_class($type));
        }
    }

    public function setBody($body): LaminasMethodGenerator
    {
        if ((string) $this->getReturnType() === 'void') {
            $body = preg_replace('/return ([^;]++;)/', '\1 return;', $body);
        }

        return parent::setBody($body);
    }
}
