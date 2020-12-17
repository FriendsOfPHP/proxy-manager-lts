<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use ProxyManagerLts\Exception\InvalidProxiedClassException;
use ProxyManagerLts\Proxy\GhostObjectInterface;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLtsTestAsset\BaseInterface;
use ProxyManagerLtsTestAsset\ClassWithAbstractPublicMethod;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingGhostGenerator}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingGhostGenerator
 * @group Coverage
 */
final class LazyLoadingGhostGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * {@inheritDoc}
     */
    public function testGeneratesValidCode(string $className): void
    {
        if (false !== strpos($className, 'TypedProp') && \PHP_VERSION_ID < 70400) {
            self::markTestSkipped('PHP 7.4 required.');
        }

        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInterface()) {
            // @todo interfaces *may* be proxied by deferring property localization to the constructor (no hardcoding)
            $this->expectException(InvalidProxiedClassException::class);
        }

        parent::testGeneratesValidCode($className);
    }

    public function testWillRejectInterfaces(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $this
            ->getProxyGenerator()
            ->generate(new ReflectionClass(BaseInterface::class), new ClassGenerator());
    }

    public function testAllAbstractMethodsWillBeMadeConcrete(): void
    {
        $classGenerator = new ClassGenerator();

        $this
            ->getProxyGenerator()
            ->generate(new ReflectionClass(ClassWithAbstractPublicMethod::class), $classGenerator);

        foreach ($classGenerator->getMethods() as $method) {
            self::assertFalse($method->isAbstract());
        }
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new LazyLoadingGhostGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [GhostObjectInterface::class];
    }
}
