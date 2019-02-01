<?php

/**
 * PackageService
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Service\Package;

use AppBundle\Service\AbstractService;
use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\DOM\DOMServiceInterface;
use AppBundle\Service\GitHub\GitHubServiceProvider;
use AppBundle\Service\PackageRepository\PackageRepositoryServiceInterface;
use AppBundle\Service\Packagist\PackagistServiceProviderInterface;
use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\Values\Content\Content;
use Netgen\TagsBundle\Core\FieldType\Tags\Value;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DomCrawler\Crawler;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;

/**
 * Class PackageService
 * @package AppBundle\Service\Package
 */
class PackageService extends AbstractService implements PackageServiceInterface
{
    static $CONTENT_TYPE_NAME = 'package';

    static $DEFAULT_LANG_CODE = 'eng-GB';

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

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var int
     */
    private $parentLocationId;

    public function __construct(
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        PackagistServiceProviderInterface $packagistServiceProvider,
        PackageRepositoryServiceInterface $packageRepositoryService,
        CacheServiceInterface $cacheService,
        DOMServiceInterface $domService,
        TagsServiceInterface $tagsService,
        int $parentLocationId
    ) {
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->packageRepositoryService = $packageRepositoryService;
        $this->cacheService = $cacheService;
        $this->domService = $domService;
        $this->tagsService = $tagsService;
        $this->parentLocationId = $parentLocationId;

        parent::__construct($contentTypeService, $contentService, $locationService);
    }

    /**
     * @param array $formData
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function addPackage(array $formData): Content
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(self::$CONTENT_TYPE_NAME);
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, self::$DEFAULT_LANG_CODE);

        $packageUrl = $formData['url'] ?? '';
        $packageName = $formData['name'] ?? '';
        $packageCategories = $formData['categories'] ?? [];

        $packageDetails = $this->getPackageDetails($this->getPackageIdFromUrl($packageUrl));

        $contentCreateStruct->setField('package_id', $packageDetails->packageId);
        $contentCreateStruct->setField('name', $packageName);
        $contentCreateStruct->setField('description', $this->getXmlString($packageDetails->description));
        $contentCreateStruct->setField('packagist_url', $packageUrl);
        $contentCreateStruct->setField('downloads', $packageDetails->downloads);
        $contentCreateStruct->setField('stars', $packageDetails->stars);
        $contentCreateStruct->setField('forks', $packageDetails->forks);
        $contentCreateStruct->setField('updated', $packageDetails->updateDate);
        $contentCreateStruct->setField('checksum', $packageDetails->checksum);
        $contentCreateStruct->setField('package_category', $this->getTagsFromCategories($packageCategories));
        $contentCreateStruct->setField('readme', $packageDetails->readme);

        $locationCreateStruct = $this->locationService->newLocationCreateStruct($this->parentLocationId);

        return $this->contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
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
            $packageDetails = $this->getPackageDetails($packageName);
            $item->expiresAfter((int) $this->cacheService->getCacheExpirationTime());
            $this->cacheService->save($item->set($packageDetails));

            return $packageDetails;
        }

        return $item->get();
    }

    /**
     * @param $packageName
     *
     * @return Package|null
     */
    private function getPackageDetails(string $packageName): ?Package
    {
        $packageName = trim($packageName);

        $packageDetails = $this->packagistServiceProvider->getPackageDetails($packageName);

        $readme = $this->packageRepositoryService->getReadme(new RepositoryMetadata($packageDetails->repository));

        if ($readme) {
            $crawler = new Crawler($readme);
            $this->domService->removeElementsFromDOM($crawler, ['.anchor', '[data-canonical-src]']);
            $this->domService->setAbsoluteURL($crawler, ['repository' => $packageDetails->repository, 'link' => GitHubServiceProvider::GITHUB_URL_PARTS]);

            $packageDetails->readme = $crawler->html();
        }

        return $packageDetails;
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

    /**
     * @param string $packagistUrl
     *
     * @return string
     */
    private function getPackageIdFromUrl(string $packagistUrl): string
    {
        $repositoryMetadata = new RepositoryMetadata($packagistUrl);

        $packageId = $repositoryMetadata->getUsername() . '/' . $repositoryMetadata->getRepositoryName();
        unset($repositoryMetadata);

        return $packageId;
    }

    /**
     * @param array $categories
     *
     * @return Value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getTagsFromCategories(array $categories): Value
    {
        $tags = [];

        foreach ($categories as $category) {
            $tags[] = $this->tagsService->loadTag($category);
        }

        return new Value($tags);
    }

    /**
     * @param $stringToXml
     *
     * @return string
     */
    private function getXmlString($stringToXml): string
    {
        $escapedString = htmlspecialchars($stringToXml, ENT_XML1);

        $xmlText = <<< EOX
<?xml version='1.0' encoding='utf-8'?>
<section 
    xmlns="http://docbook.org/ns/docbook" 
    xmlns:xlink="http://www.w3.org/1999/xlink" 
    xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" 
    xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" 
    version="5.0-variant ezpublish-1.0">
<para>{$escapedString}</para>
</section>
EOX;
        return $xmlText;
    }
}
