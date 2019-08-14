<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\ValueObject\RepositoryMetadata;

class PackageRepositoryStrategy
{
    /** @var \AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface[] */
    private $packageRepositoryServices = [];

    /**
     * @param \AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface $packageRepositoryService
     */
    public function addPackageRepositoryService(PackageRepositoryServiceInterface $packageRepositoryService): void
    {
        $this->packageRepositoryServices[] = $packageRepositoryService;
    }

    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     * @param string $format
     *
     * @return string|null
     */
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string
    {
        $repositoryService = $this->getRepositoryService($repositoryMetadata);

        return $repositoryService->getReadme($repositoryMetadata, $format);
    }

    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     *
     * @return \AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface|null
     */
    private function getRepositoryService(RepositoryMetadata $repositoryMetadata): ?PackageRepositoryServiceInterface
    {
        /** @var PackageRepositoryServiceInterface $service */
        foreach ($this->packageRepositoryServices as $service) {
            if ($service->canGetClientService($repositoryMetadata)) {
                return $service;
            }
        }

        throw new \InvalidArgumentException('Unsupported Package Repository Service');
    }
}
