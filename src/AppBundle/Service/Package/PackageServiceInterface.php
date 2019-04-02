<?php

/**
 * PackageServiceInterface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Package;

use AppBundle\ValueObject\Package;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Interface PackageServiceInterface.
 */
interface PackageServiceInterface
{
    public function getPackage(string $packageName): Package;

    public function addPackage(array $formData): Content;
}
