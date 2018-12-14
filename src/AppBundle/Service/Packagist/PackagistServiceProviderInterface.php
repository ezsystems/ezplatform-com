<?php

/**
 * PackagistServiceProviderInterface
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Packagist;

use AppBundle\ValueObject\Package;

/**
 * Interface PackagistServiceProviderInterface
 * @package AppBundle\Service\Packagist
 */
interface PackagistServiceProviderInterface
{
    public function getPackageDetails($packageName): ?Package;
}
