<?php

declare(strict_types=1);

namespace ProxyManagerLts\Factory;

use Closure;
use OutOfBoundsException;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Proxy\AccessInterceptorInterface;
use ProxyManagerLts\Proxy\AccessInterceptorValueHolderInterface;
use ProxyManagerLts\Proxy\ValueHolderInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;

use function get_class;

/**
 * Factory responsible of producing proxy objects
 */
class AccessInterceptorValueHolderFactory extends AbstractBaseFactory
{
    private $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new AccessInterceptorValueHolderGenerator();
    }

    /**
     * @param object                 $instance           the object to be wrapped within the value holder
     * @param array<string, Closure> $prefixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       before method logic is executed
     * @param array<string, Closure> $suffixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       after method logic is executed
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     *
     * @psalm-param RealObjectType $instance
     * @psalm-param array<string, callable(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   bool=
     * ) : mixed> $prefixInterceptors
     * @psalm-param array<string, callable(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   mixed=,
     *   bool=
     * ) : mixed> $suffixInterceptors
     *
     * @psalm-return RealObjectType&AccessInterceptorInterface<RealObjectType>&ValueHolderInterface<RealObjectType>&AccessInterceptorValueHolderInterface<RealObjectType>
     *
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(
        $instance,
        array $prefixInterceptors = [],
        array $suffixInterceptors = []
    ): AccessInterceptorValueHolderInterface {
        $proxyClassName = $this->generateProxy(get_class($instance));

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors);
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
