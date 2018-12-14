<?php

/**
 * MapperTest
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Packagist\Test;

use AppBundle\Service\Packagist\Mapper;
use AppBundle\Tests\Fixtures\PackageTestFixture;
use AppBundle\Tests\Fixtures\PackagistApiPackageTestFixture;
use AppBundle\ValueObject\Package;
use PHPUnit\Framework\TestCase;

/**
 * Test case for mapper.
 *
 * Class MapperTest
 *
 * @package AppBundle\Service\Packagist\Test
 */
class MapperTest extends TestCase
{
    /**
     * @var \Packagist\Api\Result\Package
     */
    protected $apiPackage;

    /**
     * @var \AppBundle\ValueObject\Package
     */
    protected $package;

    /**
     * @var \Packagist\Api\Result\Package
     */
    protected $packagistPackage;

    /**
     * @var \AppBundle\Service\Packagist\Mapper
     */
    protected $mapper;

    protected function setUp()
    {
        $packageFixture = new PackageTestFixture();
        $this->package = $packageFixture->getPackage();

        $packagistPackageFixture = new PackagistApiPackageTestFixture();
        $this->packagistPackage = $packagistPackageFixture->getPackage();
    }

    /**
     * Tests mapper without excluded maintainers
     *
     * @covers \AppBundle\Service\Packagist\Mapper::createPackageFromPackagistApiResult()
     */
    public function testCreatePackageFromPackagistApiResultWithoutExcludedMaintainer()
    {
        $this->assertEquals(
            $this->package,
            $this->getPackageFromMapper()
        );
    }

    /**
     * Tests mapper with excluded maintainers
     *
     * @covers \AppBundle\Service\Packagist\Mapper::createPackageFromPackagistApiResult()
     */
    public function testCreatePackageFromPackagistApiResultWithExcludedMaintainer()
    {
        $this->assertEquals(
            $this->package,
            $this->getPackageFromMapper(['ezrobot'])
        );
    }

    /**
     * @param array $excludedMaintainers
     *
     * @return Package
     */
    private function getPackageFromMapper(array $excludedMaintainers = []): Package
    {
        $mapper = new Mapper($excludedMaintainers);
        return $mapper->createPackageFromPackagistApiResult($this->packagistPackage);
    }
}
