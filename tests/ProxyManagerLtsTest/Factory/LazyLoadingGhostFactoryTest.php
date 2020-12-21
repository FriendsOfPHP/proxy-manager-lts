<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Autoloader\AutoloaderInterface;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Factory\LazyLoadingGhostFactory;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;
use ProxyManagerLts\Signature\ClassSignatureGeneratorInterface;
use ProxyManagerLts\Signature\SignatureCheckerInterface;
use ProxyManagerLtsTest\Assert;
use ProxyManagerLtsTestAsset\LazyLoadingMock;

/**
 * @covers \ProxyManagerLts\Factory\AbstractBaseFactory
 * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory
 * @group Coverage
 */
final class LazyLoadingGhostFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    protected $inflector;

    /** @var SignatureCheckerInterface&MockObject */
    protected $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private $classSignatureGenerator;

    /** @var Configuration&MockObject */
    protected $config;

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
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::__construct
     */
    public static function testWithOptionalFactory(): void
    {
        self::assertInstanceOf(
            Configuration::class,
            Assert::readAttribute(new LazyLoadingGhostFactory(), 'configuration')
        );
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::__construct
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::createProxy
     */
    public function testWillSkipAutoGeneration(): void
    {
        $className = UniqueIdentifierGenerator::getIdentifier('foo');

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with($className)
            ->willReturn(LazyLoadingMock::class);

        $factory     = new LazyLoadingGhostFactory($this->config);
        $initializer = static function (): bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::__construct
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::createProxy
     * @covers \ProxyManagerLts\Factory\LazyLoadingGhostFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        /** @var class-string $className */
        $className      = UniqueIdentifierGenerator::getIdentifier('foo');
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->willReturn($generator);
        $this->config->method('getProxyAutoloader')->willReturn($autoloader);

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
                eval('class ' . $proxyClassName . ' extends \\ProxyManagerLtsTestAsset\\LazyLoadingMock {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with($className)
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with($className)
            ->willReturn(LazyLoadingMock::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory     = new LazyLoadingGhostFactory($this->config);
        $initializer = static function (): bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }
}
