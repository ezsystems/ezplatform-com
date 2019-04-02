<?php

/**
 * PackageRepositoryProviderStrategy.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\ValueObject\RepositoryMetadata;

/**
 * Class PackageRepositoryStrategy.
 */
class PackageRepositoryProviderStrategy
{
    /** @var array */
    private $packageRepositoryServiceProviders = [];

    /**
     * @param \AppBundle\Service\PackageRepository\PackageRepositoryServiceProviderInterface $packageRepositoryService
     */
    public function addPackageRepositoryServiceProvider(PackageRepositoryServiceProviderInterface $packageRepositoryService): void
    {
        $this->packageRepositoryServiceProviders[] = $packageRepositoryService;
    }

    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     * @param string $format
     *
     * @return string|null
     */
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string
    {
        $provider = $this->getRepositoryProvider($repositoryMetadata);

        return $provider->getReadme($repositoryMetadata, $format);
    }

    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     *
     * @return PackageRepositoryServiceProviderInterface|null
     */
    private function getRepositoryProvider(RepositoryMetadata $repositoryMetadata): ?PackageRepositoryServiceProviderInterface
    {
        /** @var PackageRepositoryServiceProviderInterface $provider */
        foreach ($this->packageRepositoryServiceProviders as $provider) {
            if ($provider->canGetClientProvider($repositoryMetadata)) {
                return $provider;
            }
        }

        throw new \InvalidArgumentException('Unsupported Package Repository Providers');
    }
}
