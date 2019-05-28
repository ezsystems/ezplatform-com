<?php

/**
 * PackageTestFixture.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Objects;

use AppBundle\Mapper\PackageMapper;
use AppBundle\ValueObject\Package;

class PackageValueObject extends AbstractPackage
{
    /**
     * @return PackageValueObject
     */
    public function getPackage(): Package
    {
        $package = new Package();
        $package->packageId = 'test/package';
        $package->description = 'Test description';
        $package->repository = 'http://github.com/test/package';
        $package->maintainers = $this->getMaintainers();
        $package->authorAvatarUrl = PackageMapper::GITHUB_AVATAR_BASE_URL . 'test';
        $package->packageMetadata = $this->getPackageMetadata();
        $package->author = $this->getAuthor();
        $package->checksum = '1258aca3bd4a63e5b0b4cd2742d60e0e';

        return $package;
    }
}
