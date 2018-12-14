<?php

/**
 * PackageService
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Package;

use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\DOM\DOMServiceInterface;
use AppBundle\Service\GitHub\GitHubServiceProvider;
use AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface;
use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\RepositoryMetadata;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PackageService
 * @package AppBundle\Service\Package
 */
class PackageService implements PackageServiceInterface
{
    /**
     * @var PackagistServiceProviderInterface
     */
    private $packagistServiceProvider;

    /**
     * @var PackageRepositoryServiceInterface
     */
    private $packageRepositoryService;

    /**
     * @var CacheServiceInterface $cacheService
     */
    private $cacheService;

    /**
     * @var DOMServiceInterface $domService
     */
    private $domService;

    public function __construct (
        PackagistServiceProviderInterface $packagistServiceProvider,
        PackageRepositoryServiceInterface $packageRepositoryService,
        CacheServiceInterface $cacheService,
        DOMServiceInterface $domService
    ) {
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->packageRepositoryService = $packageRepositoryService;
        $this->cacheService = $cacheService;
        $this->domService = $domService;
    }

    /**
     * @param string $packageName
     * @param bool $force
     *
     * @return Package
     */
    public function getPackage(string $packageName, bool $force = false): Package
    {
        $packageName = trim($packageName);

        /**
         * @var CacheItemInterface $item
         */
        $item = $this->cacheService->getItem($this->removeReservedCharactersFromPackageName($packageName));

        if ($force || !$item->isHit()) {

            $packageDetails = $this->packagistServiceProvider->getPackageDetails($packageName);

            $readme = $this->packageRepositoryService->getReadme(new RepositoryMetadata($packageDetails->repository));

            if ($readme) {
                $crawler = new Crawler($readme);
                $this->domService->removeElementsFromDOM($crawler, ['.anchor', '[data-canonical-src]']);
                $this->domService->setAbsoluteURL($crawler, ['repository' => $packageDetails->repository, 'link' => GitHubServiceProvider::GITHUB_URL_PARTS]);

                $packageDetails->readme = $crawler->html();
            }

            $item->expiresAfter((int) $this->cacheService->getCacheExpirationTime());
            $this->cacheService->save($item->set($packageDetails));

            return $packageDetails;
        }

        return $item->get();
    }

    /**
     * @param string $packageName
     *
     * @return string
     */
    private function removeReservedCharactersFromPackageName(string $packageName): string
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '-', $packageName);
    }
}
