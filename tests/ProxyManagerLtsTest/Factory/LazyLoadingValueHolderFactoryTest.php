<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Autoloader\AutoloaderInterface;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Factory\LazyLoadingValueHolderFactory;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerLts\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;
use ProxyManagerLts\Signature\ClassSignatureGeneratorInterface;
use ProxyManagerLts\Signature\SignatureCheckerInterface;
use ProxyManagerLtsTest\Assert;
use ProxyManagerLtsTestAsset\EmptyClass;
use ProxyManagerLtsTestAsset\LazyLoadingMock;

use function get_class;

/**
 * @covers \ProxyManagerLts\Factory\AbstractBaseFactory
 * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory
 * @group Coverage
 */
final class LazyLoadingValueHolderFactoryTest extends TestCase
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
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::__construct
     */
    public static function testWithOptionalFactory(): void
    {
        self::assertInstanceOf(
            Configuration::class,
            Assert::readAttribute(new LazyLoadingValueHolderFactory(), 'configuration')
        );
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::__construct
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::createProxy
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

        $factory     = new LazyLoadingValueHolderFactory($this->config);
        $initializer = static function (): bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::__construct
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::createProxy
     * @covers \ProxyManagerLts\Factory\LazyLoadingValueHolderFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $className      = UniqueIdentifierGenerator::getIdentifier('foo');
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
            ->willReturn(EmptyClass::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory     = new LazyLoadingValueHolderFactory($this->config);
        $initializer = static function (): bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertInstanceOf($proxyClassName, $proxy);

        self::assertSame($proxyClassName, get_class($proxy));
        self::assertSame($initializer, $proxy->getProxyInitializer());
    }
}
