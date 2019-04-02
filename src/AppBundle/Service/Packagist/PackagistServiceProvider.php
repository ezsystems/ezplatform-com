<?php

/**
 * PackagistServiceProvider.
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Packagist;

use AppBundle\Mapper\PackageMapper;
use AppBundle\ValueObject\Package;
use Packagist\Api\Client;

/**
 * Class PackagistServiceProvider.
 */
class PackagistServiceProvider implements PackagistServiceProviderInterface
{
    /** @var \Packagist\Api\Client */
    private $packagistClient;

    /** @var \AppBundle\Mapper\PackageMapper */
    private $mapper;

    /**
     * PackagistServiceProvider constructor.
     *
     * @param \Packagist\Api\Client $packagistClient
     * @param \AppBundle\Mapper\PackageMapper $mapper
     */
    public function __construct(
        Client $packagistClient,
        PackageMapper $mapper
    ) {
        $this->packagistClient = $packagistClient;
        $this->mapper = $mapper;
    }

    /**
     * @param $packageName
     *
     * @return \AppBundle\ValueObject\Package|null
     */
    public function getPackageDetails($packageName): ?Package
    {
        try {
            return $this->mapper->createPackageFromPackagistApiResult($this->packagistClient->get($packageName));
        } catch (\Exception $exception) {
            return null;
        }
    }
}
