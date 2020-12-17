<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManagerLts\Exception\ExceptionInterface;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManagerLts\Proxy\ProxyInterface;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use ProxyManagerLts\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerLts\ProxyGenerator\NullObjectGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\ProxyGenerator\RemoteObjectGenerator;
use ProxyManagerLts\Signature\ClassSignatureGenerator;
use ProxyManagerLts\Signature\SignatureGenerator;
use ReflectionClass;
use ReflectionException;

use function array_filter;
use function array_map;
use function array_merge;
use function get_declared_classes;
use function realpath;
use function strpos;
use function uniqid;

/**
 * Verifies that proxy-manager will not attempt to `eval()` code that will cause fatal errors
 *
 * @group Functional
 * @coversNothing
 */
final class FatalPreventionFunctionalTest extends TestCase
{
    /**
     * Verifies that code generation and evaluation will not cause fatals with any given class
     *
     * @param string $generatorClass an instantiable class (no arguments) implementing
     *                               the {@see \ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface}
     * @param string $className      a valid (existing/autoloadable) class name
     *
     * @dataProvider getTestedClasses
     *
     * @psalm-param class-string<ProxyGeneratorInterface> $generatorClass
     * @psalm-param class-string                          $className
     */
    public function testCodeGeneration(string $generatorClass, string $className): void
    {
        $generatedClass          = new ClassGenerator(uniqid('generated', true));
        $generatorStrategy       = new EvaluatingGeneratorStrategy();
        $classGenerator          = new $generatorClass();
        $classSignatureGenerator = new ClassSignatureGenerator(new SignatureGenerator());

        try {
            $classGenerator->generate(new ReflectionClass($className), $generatedClass);
            $classSignatureGenerator->addSignature($generatedClass, ['key' => 'eval tests']);
            $generatorStrategy->generate($generatedClass);
        } catch (ExceptionInterface $e) {
            // empty catch: this is actually a supported failure
        } catch (ReflectionException $e) {
            // empty catch: this is actually a supported failure
        }

        self::assertTrue(true, 'Code generation succeeded: proxy is valid or couldn\'t be generated at all');
    }

    /**
     * @return string[][]
     *
     * @psalm-return array<int, array<int, class-string<ProxyGeneratorInterface>|class-string>>
     */
    public function getTestedClasses(): array
    {
        return array_merge(
            [],
            ...array_map(
                function ($generator): array {
                    return array_map(
                        static function ($class) use ($generator): array {
                            return [$generator, $class];
                        },
                        $this->getProxyTestedClasses()
                    );
                },
                [
                    AccessInterceptorScopeLocalizerGenerator::class,
                    AccessInterceptorValueHolderGenerator::class,
                    LazyLoadingGhostGenerator::class,
                    LazyLoadingValueHolderGenerator::class,
                    NullObjectGenerator::class,
                    RemoteObjectGenerator::class,
                ]
            )
        );
    }

    /**
     * @return string[]
     *
     * @psalm-return array<int, class-string>
     *
     * @private (public only for PHP 5.3 compatibility)
     */
    private function getProxyTestedClasses(): array
    {
        $skippedPaths = [
            realpath(__DIR__ . '/../../../src'),
            realpath(__DIR__ . '/../../../vendor'),
            realpath(__DIR__ . '/../../ProxyManagerLtsTest'),
        ];

        return array_filter(
            get_declared_classes(),
            static function ($className) use ($skippedPaths): bool {
                $reflectionClass = new ReflectionClass($className);

                $fileName = $reflectionClass->getFileName();

                if (! $fileName) {
                    return false;
                }

                if ($reflectionClass->implementsInterface(ProxyInterface::class)) {
                    return false;
                }

                $realPath = realpath($fileName);

                self::assertIsString($realPath);

                foreach ($skippedPaths as $skippedPath) {
                    self::assertIsString($skippedPath);

                    if (strpos($realPath, $skippedPath) === 0) {
                        // skip classes defined within ProxyManagerLts, vendor or the test suite
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
