<?php
/**
 * PackagistApiPackageTestFixture
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Tests\Fixtures;

use Packagist\Api\Result\Package;

/**
 * Class PackagistApiPackageTestFixture
 *
 * @package AppBundle\Tests\Fixtures
 */
class PackagistApiPackageTestFixture extends AbstractPackageTestFixture
{
    /**
     * @return \Packagist\Api\Result\Package
     */
    public function getPackage(): Package
    {
        $package = new Package();
        $package->fromArray([
                'name' => 'test/package',
                'description' => 'Test description',
                'time' => '2017-11-01T19:00:03+00:00',
                'maintainers' => $this->getMaintainers(),
                'versions' => [
                    $this->getVersion(),
                ],
                'type' => '',
                'repository' => 'http://github.com/test/package',
                'downloads' => $this->getDownloads(),
                'favers' => '',
                'abandoned' => false,
                'suggesters' => 0,
                'dependents' => 0,
                'githubStars' => 12,
                'githubForks' => 3,
            ]);

        return $package;
    }
}
