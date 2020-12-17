<?php

declare(strict_types=1);

namespace ProxyManagerLts;

use ProxyManagerLts\Autoloader\Autoloader;
use ProxyManagerLts\Autoloader\AutoloaderInterface;
use ProxyManagerLts\FileLocator\FileLocator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManagerLts\Inflector\ClassNameInflector;
use ProxyManagerLts\Inflector\ClassNameInflectorInterface;
use ProxyManagerLts\Signature\ClassSignatureGenerator;
use ProxyManagerLts\Signature\ClassSignatureGeneratorInterface;
use ProxyManagerLts\Signature\SignatureChecker;
use ProxyManagerLts\Signature\SignatureCheckerInterface;
use ProxyManagerLts\Signature\SignatureGenerator;
use ProxyManagerLts\Signature\SignatureGeneratorInterface;

use function sys_get_temp_dir;

/**
 * Base configuration class for the proxy manager - serves as micro disposable DIC/facade
 */
class Configuration
{
    public const DEFAULT_PROXY_NAMESPACE = 'ProxyManagerLtsGeneratedProxy';

    protected $proxiesTargetDir;
    protected $proxiesNamespace = self::DEFAULT_PROXY_NAMESPACE;
    protected $generatorStrategy;
    protected $proxyAutoloader;
    protected $classNameInflector;
    protected $signatureGenerator;
    protected $signatureChecker;
    protected $classSignatureGenerator;

    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader): void
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    public function getProxyAutoloader(): AutoloaderInterface
    {
        return $this->proxyAutoloader
            ?? $this->proxyAutoloader = new Autoloader(
                new FileLocator($this->getProxiesTargetDir()),
                $this->getClassNameInflector()
            );
    }

    public function setProxiesNamespace(string $proxiesNamespace): void
    {
        $this->proxiesNamespace = $proxiesNamespace;
    }

    public function getProxiesNamespace(): string
    {
        return $this->proxiesNamespace;
    }

    public function setProxiesTargetDir(string $proxiesTargetDir): void
    {
        $this->proxiesTargetDir = $proxiesTargetDir;
    }

    public function getProxiesTargetDir(): string
    {
        return $this->proxiesTargetDir
            ?? $this->proxiesTargetDir = sys_get_temp_dir();
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy): void
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    public function getGeneratorStrategy(): GeneratorStrategyInterface
    {
        return $this->generatorStrategy
            ?? $this->generatorStrategy = new EvaluatingGeneratorStrategy();
    }

    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector): void
    {
        $this->classNameInflector = $classNameInflector;
    }

    public function getClassNameInflector(): ClassNameInflectorInterface
    {
        return $this->classNameInflector
            ?? $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
    }

    public function setSignatureGenerator(SignatureGeneratorInterface $signatureGenerator): void
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    public function getSignatureGenerator(): SignatureGeneratorInterface
    {
        return $this->signatureGenerator
            ?? $this->signatureGenerator = new SignatureGenerator();
    }

    public function setSignatureChecker(SignatureCheckerInterface $signatureChecker): void
    {
        $this->signatureChecker = $signatureChecker;
    }

    public function getSignatureChecker(): SignatureCheckerInterface
    {
        return $this->signatureChecker
            ?? $this->signatureChecker = new SignatureChecker($this->getSignatureGenerator());
    }

    public function setClassSignatureGenerator(ClassSignatureGeneratorInterface $classSignatureGenerator): void
    {
        $this->classSignatureGenerator = $classSignatureGenerator;
    }

    public function getClassSignatureGenerator(): ClassSignatureGeneratorInterface
    {
        return $this->classSignatureGenerator
            ?? $this->classSignatureGenerator = new ClassSignatureGenerator($this->getSignatureGenerator());
    }
}
