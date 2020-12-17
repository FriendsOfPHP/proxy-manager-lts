<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Autoloader\AutoloaderInterface;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Factory\NullObjectFactory;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;
use ProxyManagerLts\Signature\ClassSignatureGeneratorInterface;
use ProxyManagerLts\Signature\SignatureCheckerInterface;
use ProxyManagerLtsTestAsset\NullObjectMock;
use stdClass;

/**
 * @covers \ProxyManagerLts\Factory\AbstractBaseFactory
 * @covers \ProxyManagerLts\Factory\NullObjectFactory
 * @group Coverage
 */
final class NullObjectFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    private $inflector;

    /** @var SignatureCheckerInterface&MockObject */
    private $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private $classSignatureGenerator;

    /** @var Configuration&MockObject */
    private $config;

    protected function setUp(): void
    {
        $this->config                  = $this->createMock(Configuration::class);
        $this->inflector               = $this->createMock(ClassNameInflectorInterface::class);
        $this->signatureChecker        = $this->createMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this
            ->config
            ->method('getClassNameInflector')
            ->willReturn($this->inflector);

        $this
            ->config
            ->method('getSignatureChecker')
            ->willReturn($this->signatureChecker);

        $this
            ->config
            ->method('getClassSignatureGenerator')
            ->willReturn($this->classSignatureGenerator);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::__construct
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::createProxy
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::getGenerator
     */
    public function testWillSkipAutoGeneration(): void
    {
        $instance = new stdClass();

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->willReturn(NullObjectMock::class);

        (new NullObjectFactory($this->config))->createProxy($instance);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::__construct
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::createProxy
     * @covers \ProxyManagerLts\Factory\NullObjectFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $instance       = new stdClass();
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->will(self::returnValue($generator));
        $this->config->method('getProxyAutoloader')->will(self::returnValue($autoloader));

        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(
                    static function (ClassGenerator $targetClass) use ($proxyClassName): bool {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->willReturnCallback(static function () use ($proxyClassName): bool {
                eval('class ' . $proxyClassName . ' extends \\ProxyManagerLtsTestAsset\\NullObjectMock {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with('stdClass')
            ->willReturn(NullObjectMock::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory = new NullObjectFactory($this->config);
        $factory->createProxy($instance);
    }
}
