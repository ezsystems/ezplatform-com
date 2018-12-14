<?php

/**
 * PackageServiceInterface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Package;

use AppBundle\ValueObject\Package;

/**
 * Interface PackageServiceInterface
 * @package AppBundle\Service\Package
 */
interface PackageServiceInterface
{
    public function getPackage(string $packageName): Package;
}
