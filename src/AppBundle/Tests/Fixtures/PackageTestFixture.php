<?php

/**
 * PackageTestFixture
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Tests\Fixtures;

use AppBundle\Service\Packagist\Mapper;
use AppBundle\ValueObject\Package;

/**
 * Class PackageTestFixture
 *
 * @package AppBundle\Tests\Fixtures
 */
class PackageTestFixture extends AbstractPackageTestFixture
{
    /**
     * @return Package
     */
    public function getPackage(): Package
    {
        $package = new Package();
        $package->packageId = 'test/package';
        $package->description = 'Test description';
        $package->repository = 'http://github.com/test/package';
        $package->downloads = 222;
        $package->maintainers = $this->getMaintainers();
        $package->authorAvatarUrl = Mapper::GITHUB_AVATAR_BASE_URL.'test';
        $package->forks = 3;
        $package->stars = 12;
        $package->author = $this->getAuthor();
        $package->creationDate = \DateTime::createFromFormat(\DateTime::ISO8601, '2017-11-01T19:00:03+00:00');
        $package->updateDate = \DateTime::createFromFormat(\DateTime::ISO8601, '2017-11-03T19:51:03+00:00');
        $package->checksum = '1258aca3bd4a63e5b0b4cd2742d60e0e';

        return $package;
    }
}
