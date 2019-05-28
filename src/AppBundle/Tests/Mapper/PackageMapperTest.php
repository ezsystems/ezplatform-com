<?php

/**
 * PackageMapperTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Mapper;

use AppBundle\Mapper\PackageMapper;
use AppBundle\Tests\Objects\PackageValueObject;
use AppBundle\Tests\Objects\PackagistApiPackage;
use AppBundle\ValueObject\Package;
use PHPUnit\Framework\TestCase;

class PackageMapperTest extends TestCase
{
    /** @var \Packagist\Api\Result\Package */
    protected $apiPackage;

    /** @var \AppBundle\ValueObject\Package */
    protected $package;

    /** @var \Packagist\Api\Result\Package */
    protected $packagistPackage;

    protected function setUp()
    {
        $packageFixture = new PackageValueObject();
        $this->package = $packageFixture->getPackage();

        $packagistPackageFixture = new PackagistApiPackage();
        $this->packagistPackage = $packagistPackageFixture->getPackage();
    }

    /**
     * Tests mapper without excluded maintainers.
     *
     * @covers \AppBundle\Mapper\PackageMapper::createPackageFromPackagistApiResult()
     */
    public function testCreatePackageFromPackagistApiResultWithoutExcludedMaintainer()
    {
        $this->assertEquals(
            $this->package,
            $this->getPackageFromMapper()
        );
    }

    /**
     * Tests mapper with excluded maintainers.
     *
     * @covers \AppBundle\Mapper\PackageMapper::createPackageFromPackagistApiResult()
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
     * @return PackageValueObject
     */
    private function getPackageFromMapper(array $excludedMaintainers = []): Package
    {
        $mapper = new PackageMapper($excludedMaintainers);

        return $mapper->createPackageFromPackagistApiResult($this->packagistPackage);
    }
}
