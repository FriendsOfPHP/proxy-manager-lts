<?php

declare(strict_types=1);

namespace ProxyManagerLts\ProxyGenerator\NullObject\MethodGenerator;

use Laminas\Code\Reflection\MethodReflection;
use ProxyManagerLts\Generator\MethodGenerator;
use ProxyManagerLts\Generator\Util\IdentifierSuffixer;

/**
 * Method decorator for null objects
 */
class NullObjectMethodInterceptor extends MethodGenerator
{
    /**
     * @return static
     */
    public static function generateMethod(MethodReflection $originalMethod): self
    {
        /** @var static $method */
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);

        if ($originalMethod->returnsReference()) {
            $reference = IdentifierSuffixer::getIdentifier('ref');

            $method->setBody("\$reference = null;\nreturn \$" . $reference . ';');
        }

        return $method;
    }
}
