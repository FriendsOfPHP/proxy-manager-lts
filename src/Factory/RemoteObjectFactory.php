<?php

declare(strict_types=1);

namespace ProxyManagerLts\Factory;

use OutOfBoundsException;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Factory\RemoteObject\AdapterInterface;
use ProxyManagerLts\Proxy\RemoteObjectInterface;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\ProxyGenerator\RemoteObjectGenerator;
use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;

use function get_class;
use function is_object;

/**
 * Factory responsible of producing remote proxy objects
 */
class RemoteObjectFactory extends AbstractBaseFactory
{
    protected $adapter;
    private $generator;

    /**
     * {@inheritDoc}
     *
     * @param AdapterInterface $adapter
     * @param Configuration    $configuration
     */
    public function __construct(AdapterInterface $adapter, ?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->adapter   = $adapter;
        $this->generator = new RemoteObjectGenerator();
    }

    /**
     * @param string|object $instanceOrClassName
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     *
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&RemoteObjectInterface
     *
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy($instanceOrClassName): RemoteObjectInterface
    {
        $proxyClassName = $this->generateProxy(
            is_object($instanceOrClassName) ? get_class($instanceOrClassName) : $instanceOrClassName
        );

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($this->adapter);
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator ?? $this->generator = new RemoteObjectGenerator();
    }
}
