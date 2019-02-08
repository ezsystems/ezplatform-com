<?php

/**
 * PackageRepositoryProviderStrategy
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\ValueObject\RepositoryMetadata;

/**
 * Class PackageRepositoryStrategy
 *
 * @package AppBundle\Service\PackageRepository
 */
class PackageRepositoryProviderStrategy
{
    /**
     * @var array
     */
    private $packageRepositoryServiceProviders = [];

    /**
     * @param PackageRepositoryServiceProviderInterface $packageRepositoryService
     */
    public function addPackageRepositoryServiceProvider(PackageRepositoryServiceProviderInterface $packageRepositoryService)
    {
        $this->packageRepositoryServiceProviders[] = $packageRepositoryService;
    }

    /**
     * @param RepositoryMetadata $repositoryMetadata
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
     * @param RepositoryMetadata $repositoryMetadata
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
