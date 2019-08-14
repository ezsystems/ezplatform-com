<?php

/**
 * Provides method to call Repositories API e.g.: GitHub.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\ValueObject\RepositoryMetadata;

interface PackageRepositoryServiceInterface
{
    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     * @param string $format
     *
     * @return null|string
     */
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string;

    /**
     * @param \AppBundle\ValueObject\RepositoryMetadata $repositoryMetadata
     *
     * @return bool
     */
    public function canGetClientService(RepositoryMetadata $repositoryMetadata): bool;
}
