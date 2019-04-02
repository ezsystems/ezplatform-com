<?php

/**
 * PackageRepositoryServiceInterface.
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\ValueObject\RepositoryMetadata;

/**
 * Interface PackageRepositoryServiceInterface.
 */
interface PackageRepositoryServiceProviderInterface
{
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string;

    public function canGetClientProvider(RepositoryMetadata $repositoryMetadata): bool;
}
