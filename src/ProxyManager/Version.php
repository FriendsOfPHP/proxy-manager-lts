<?php

declare(strict_types=1);

namespace ProxyManager;

use Composer\InstalledVersions;
use PackageVersions\Versions;
use OutOfBoundsException;

use function class_exists;

/**
 * Version class
 *
 * Note that we cannot check the version at runtime via `git` because that would cause a lot of I/O operations.
 */
final class Version
{
    /**
     * Private constructor - this class is not meant to be instantiated
     */
    private function __construct()
    {
    }

    /**
     * Retrieves the package version in the format <detected-version>@<commit-hash>,
     * where the detected version is what composer could detect.
     *
     * @throws OutOfBoundsException
     *
     * @psalm-pure
     *
     * @psalm-suppress MixedOperand `composer-runtime-api:^2` has rough if no type declarations at all - we'll live with it
     * @psalm-suppress ImpureMethodCall `composer-runtime-api:^2` has rough if no type declarations at all - we'll live with it
     */
    public static function getVersion(): string
    {
        if (class_exists(InstalledVersions::class)) {
            return InstalledVersions::getPrettyVersion('friendsofphp/proxy-manager-lts')
                . '@' . InstalledVersions::getReference('friendsofphp/proxy-manager-lts');
        }

        if (class_exists(Versions::class)) {
            return Versions::getVersion('friendsofphp/proxy-manager-lts');
        }

        return '1@friendsofphp/proxy-manager-lts';
    }
}
