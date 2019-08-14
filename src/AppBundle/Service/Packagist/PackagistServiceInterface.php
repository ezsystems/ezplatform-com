<?php

/**
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Packagist;

use AppBundle\ValueObject\Package;

interface PackagistServiceInterface
{
    /**
     * @param string $packageName
     *
     * @return \AppBundle\ValueObject\Package|null
     */
    public function getPackageDetails(string $packageName): ?Package;
}
