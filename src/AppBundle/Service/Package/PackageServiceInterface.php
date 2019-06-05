<?php

/**
 * PackageServiceInterface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Package;

use AppBundle\Model\PackageForm;
use AppBundle\ValueObject\Package;
use eZ\Publish\API\Repository\Values\Content\Content;

interface PackageServiceInterface
{
    /**
     * @param \AppBundle\Model\PackageForm $package
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function addPackage(PackageForm $package): Content;

    /**
     * @param string $packageName
     * @param bool $force
     *
     * @return \AppBundle\ValueObject\Package
     */
    public function getPackage(string $packageName): Package;

    /**
     * @param string $packageName
     *
     * @return \AppBundle\ValueObject\Package|null
     */
    public function getPackageFromPackagist(string $packageName): ?Package;

    /**
     * @param string $packageName
     *
     * @return string
     */
    public function removeReservedCharactersFromPackageName(string $packageName): string;
}
