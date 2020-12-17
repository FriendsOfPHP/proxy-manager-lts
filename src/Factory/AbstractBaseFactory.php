<?php

declare(strict_types=1);

namespace ProxyManagerLts\Factory;

use Composer\InstalledVersions;
use OutOfBoundsException;
use PackageVersions\Versions;
use ProxyManagerLts\Configuration;
use ProxyManagerLts\Generator\ClassGenerator;
use ProxyManagerLts\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerLts\Signature\Exception\InvalidSignatureException;
use ProxyManagerLts\Signature\Exception\MissingSignatureException;
use ProxyManagerLts\Version;
use ReflectionClass;

use function array_key_exists;
use function assert;
use function class_exists;
use function is_a;

/**
 * Base factory common logic
 */
abstract class AbstractBaseFactory
{
    protected $configuration;

    /**
     * Cached checked class names
     *
     * @var array<string, string>
     * @psalm-var array<class-string, class-string>
     */
    private $checkedClasses = [];

    public function __construct(?Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration();
    }

    /**
     * Generate a proxy from a class name
     *
     * @param array<string, mixed> $proxyOptions
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     *
     * @psalm-param class-string<RealObjectType> $className
     *
     * @psalm-return class-string<RealObjectType>
     */
    protected function generateProxy(string $className, array $proxyOptions = []): string
    {
        if (array_key_exists($className, $this->checkedClasses)) {
            $generatedClassName = $this->checkedClasses[$className];

            assert(is_a($generatedClassName, $className, true));

            return $generatedClassName;
        }


        if (class_exists(InstalledVersions::class)) {
            $proxyManagerVersion = InstalledVersions::getPrettyVersion('friendsofphp/proxy-manager-lts')
                . '@' . InstalledVersions::getReference('friendsofphp/proxy-manager-lts');
        } elseif (class_exists(Versions::class)) {
            $proxyManagerVersion = Versions::getVersion('friendsofphp/proxy-manager-lts');
        } else {
            $proxyManagerVersion = '1.99.99@friendsofphp/proxy-manager-lts';
        }


        $proxyParameters = [
            'className'           => $className,
            'factory'             => static::class,
            'proxyManagerVersion' => $proxyManagerVersion,
            'proxyOptions'        => $proxyOptions,
        ];
        $proxyClassName  = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($className, $proxyParameters);

        if (! class_exists($proxyClassName)) {
            $this->generateProxyClass(
                $proxyClassName,
                $className,
                $proxyParameters,
                $proxyOptions
            );
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$className] = $proxyClassName;
    }

    abstract protected function getGenerator(): ProxyGeneratorInterface;

    /**
     * Generates the provided `$proxyClassName` from the given `$className` and `$proxyParameters`
     *
     * @param array<string, mixed> $proxyParameters
     * @param array<string, mixed> $proxyOptions
     *
     * @psalm-param class-string $proxyClassName
     * @psalm-param class-string $className
     */
    private function generateProxyClass(
        string $proxyClassName,
        string $className,
        array $proxyParameters,
        array $proxyOptions = []
    ): void {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($className);
        $phpClass  = new ClassGenerator($proxyClassName);

        /** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
        $this->getGenerator()->generate(new ReflectionClass($className), $phpClass, $proxyOptions);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        /** @psalm-suppress TooManyArguments - generator interface was not updated due to BC compliance */
        $this->configuration->getGeneratorStrategy()->generate($phpClass, $proxyOptions);

        $autoloader = $this->configuration->getProxyAutoloader();

        $autoloader($proxyClassName);
    }
}
