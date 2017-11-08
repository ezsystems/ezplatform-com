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

use Tedivm\StashBundle\Service\CacheService;
use Packagist\Api\Client;
use Guzzle\Http\Exception\CurlException;

class PackagistServiceProvider implements PackagistServiceProviderInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    private $cache;

    /**
     * @var \Packagist\Api\Client
     */
    private $packagistClient;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var int
     */
    private $cacheExpirationTime;

    /**
     * PackagistServiceProvider constructor.
     * @param CacheService $cacheService
     * @param Client $packagistClient
     * @param Mapper $mapper
     * @param $cacheExpirationTime
     */
    public function __construct(
        CacheService $cacheService,
        Client $packagistClient,
        Mapper $mapper,
        $cacheExpirationTime
    ) {
        $this->cache = $cacheService;
        $this->packagistClient = $packagistClient;
        $this->mapper = $mapper;
        $this->cacheExpirationTime = $cacheExpirationTime;
    }

    /**
     * @param string $packageName
     * @param bool $force
     * @return Package
     */
    public function getPackageDetails($packageName, $force = false)
    {
        try {
            $packageName = trim($packageName);
            $item = $this->cache->getItem($packageName);
            if ($force || $item->isMiss()) {
                $packageDetails = $this->callApi($packageName);
                $item->expiresAfter($this->cacheExpirationTime);
                $this->cache->save($item->set($packageDetails));

                return $packageDetails;
            }

            return $item->get();
        } catch (CurlException $curlException) {
            return [];
        }
    }

    /**
     * @param string $packageName
     * @return Package
     */
    private function callApi($packageName)
    {
        $packageDetails = $this->mapper->createPackageFromPackagistApiResult($this->packagistClient->get($packageName));

        return $packageDetails;
    }
}
