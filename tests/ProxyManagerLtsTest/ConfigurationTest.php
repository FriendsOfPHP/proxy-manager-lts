<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Autoloader\AutoloaderInterface;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;
use ProxyManagerLts\Signature\ClassSignatureGeneratorInterface;
use ProxyManagerLts\Signature\SignatureCheckerInterface;
use ProxyManagerLts\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \ProxyManagerLts\Configuration}
 *
 * @group Coverage
 */
final class ConfigurationTest extends TestCase
{
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getProxiesNamespace
     * @covers \ProxyManagerLts\Configuration::setProxiesNamespace
     */
    public function testGetSetProxiesNamespace(): void
    {
        self::assertSame(
            'ProxyManagerLtsGeneratedProxy',
            $this->configuration->getProxiesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setProxiesNamespace('foo');
        self::assertSame('foo', $this->configuration->getProxiesNamespace());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getClassNameInflector
     * @covers \ProxyManagerLts\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        self::assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getGeneratorStrategy
     */
    public function testDefaultGeneratorStrategyNeedToBeAInstanceOfEvaluatingGeneratorStrategy(): void
    {
        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getGeneratorStrategy
     * @covers \ProxyManagerLts\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        $strategy = $this->createMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        self::assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getProxiesTargetDir
     * @covers \ProxyManagerLts\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir(): void
    {
        self::assertDirectoryExists($this->configuration->getProxiesTargetDir());

        $this->configuration->setProxiesTargetDir(__DIR__);
        self::assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getProxyAutoloader
     * @covers \ProxyManagerLts\Configuration::setProxyAutoloader
     */
    public function testSetGetProxyAutoloader(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(AutoloaderInterface::class, $this->configuration->getProxyAutoloader());

        $autoloader = $this->createMock(AutoloaderInterface::class);

        $this->configuration->setProxyAutoloader($autoloader);
        self::assertSame($autoloader, $this->configuration->getProxyAutoloader());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getSignatureGenerator
     * @covers \ProxyManagerLts\Configuration::setSignatureGenerator
     */
    public function testSetGetSignatureGenerator(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);

        $this->configuration->setSignatureGenerator($signatureGenerator);
        self::assertSame($signatureGenerator, $this->configuration->getSignatureGenerator());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getSignatureChecker
     * @covers \ProxyManagerLts\Configuration::setSignatureChecker
     */
    public function testSetGetSignatureChecker(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureChecker = $this->createMock(SignatureCheckerInterface::class);

        $this->configuration->setSignatureChecker($signatureChecker);
        self::assertSame($signatureChecker, $this->configuration->getSignatureChecker());
    }

    /**
     * @covers \ProxyManagerLts\Configuration::getClassSignatureGenerator
     * @covers \ProxyManagerLts\Configuration::setClassSignatureGenerator
     */
    public function testSetGetClassSignatureGenerator(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(
            ClassSignatureGeneratorInterface::class,
            $this->configuration->getClassSignatureGenerator()
        );
        $classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this->configuration->setClassSignatureGenerator($classSignatureGenerator);
        self::assertSame($classSignatureGenerator, $this->configuration->getClassSignatureGenerator());
    }
}
