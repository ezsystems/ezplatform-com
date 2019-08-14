<?php

/**
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Packagist;

use AppBundle\Helper\LoggerTrait;
use AppBundle\Mapper\PackageMapper;
use AppBundle\ValueObject\Package;
use Packagist\Api\Client;

class PackagistService implements PackagistServiceInterface
{
    use LoggerTrait;

    /** @var \Packagist\Api\Client */
    private $packagistClient;

    /** @var \AppBundle\Mapper\PackageMapper */
    private $mapper;

    /***
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
     * {@inheritdoc}
     */
    public function getPackageDetails(string $packageName): ?Package
    {
        try {
            return $this->mapper->createPackageFromPackagistApiResult($this->packagistClient->get($packageName));
        } catch (\Exception $exception) {
            $this->logError(
                sprintf('Packagist API Exception: %s | PackageName: %s', $exception->getMessage(), $packageName)
            );
            return null;
        }
    }
}
