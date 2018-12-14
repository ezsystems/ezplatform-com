<?php

/**
 * PackagistServiceProvider
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Packagist;

use AppBundle\ValueObject\Package;
use Packagist\Api\Client;
use Guzzle\Http\Exception\CurlException;

/**
 * Class PackagistServiceProvider
 * @package AppBundle\Service\Packagist
 */
class PackagistServiceProvider implements PackagistServiceProviderInterface
{
    /**
     * @var \Packagist\Api\Client
     */
    private $packagistClient;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * PackagistServiceProvider constructor.
     *
     * @param Client $packagistClient
     * @param Mapper $mapper
     */
    public function __construct(
        Client $packagistClient,
        Mapper $mapper
    ) {
        $this->packagistClient = $packagistClient;
        $this->mapper = $mapper;
    }

    /**
     * @param $packageName
     * @return Package|null
     */
    public function getPackageDetails($packageName): ?Package
    {
        try {
            return $this->mapper->createPackageFromPackagistApiResult($this->packagistClient->get($packageName));
        } catch (CurlException $curlException) {
            return null;
        }
    }
}
