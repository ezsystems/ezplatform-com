<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Package;

use AppBundle\Helper\RichTextHelper;
use AppBundle\Service\AbstractService;
use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\DOM\DOMServiceInterface;
use AppBundle\Service\PackageRepository\GitHubService;
use AppBundle\Service\PackageRepository\GitLabService;
use AppBundle\Service\PackageRepository\PackageRepositoryStrategy;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use AppBundle\Service\Packagist\PackagistServiceInterface;
use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\RepositoryMetadata;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\TagsBundle\Core\FieldType\Tags\Value;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\DomCrawler\Crawler;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use AppBundle\Model\PackageForm;

class PackageService extends AbstractService implements PackageServiceInterface
{
    public const CONTENT_TYPE_NAME = 'package';
    public const DEFAULT_LANG_CODE = 'eng-GB';
    private const REPOSITORY_PLATFORMS = [
        'github' => GitHubService::GITHUB_URL_PARTS,
    ];

    /** @var \AppBundle\Service\Packagist\PackagistServiceInterface */
    private $packagistService;

    /** @var \AppBundle\Service\PackageRepository\PackageRepositoryStrategy */
    private $packageRepository;

    /** @var \AppBundle\Service\Cache\CacheServiceInterface */
    private $cacheService;

    /** @var \AppBundle\Service\DOM\DOMServiceInterface */
    private $domService;

    /** @var \Netgen\TagsBundle\API\Repository\TagsService */
    private $tagsService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \AppBundle\Helper\RichTextHelper */
    private $richTextHelper;

    /** @var int */
    private $parentLocationId;

    /** @var int */
    private $packageContributorId;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \AppBundle\Service\Packagist\PackagistServiceInterface $packagistService
     * @param \AppBundle\Service\PackageRepository\PackageRepositoryStrategy $packageRepository
     * @param \AppBundle\Service\Cache\CacheServiceInterface $cacheService
     * @param \AppBundle\Service\DOM\DOMServiceInterface $domService
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \AppBundle\Helper\RichTextHelper $richTextHelper
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        PackagistServiceInterface $packagistService,
        PackageRepositoryStrategy $packageRepository,
        CacheServiceInterface $cacheService,
        DOMServiceInterface $domService,
        TagsServiceInterface $tagsService,
        ConfigResolverInterface $configResolver,
        RichTextHelper $richTextHelper
    ) {
        $this->packagistService = $packagistService;
        $this->packageRepository = $packageRepository;
        $this->cacheService = $cacheService;
        $this->domService = $domService;
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
        $this->richTextHelper = $richTextHelper;
        $this->parentLocationId = $this->configResolver->getParameter('package_list_location_id', 'app');
        $this->packageContributorId = $this->configResolver->getParameter('package_contributor_id', 'app');

        parent::__construct($permissionResolver, $userService, $contentTypeService, $contentService, $locationService);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function addPackage(PackageForm $package): Content
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(self::CONTENT_TYPE_NAME);
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, self::DEFAULT_LANG_CODE);

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser($this->packageContributorId)
        );

        $repositoryMetadata = new RepositoryMetadata($package->getUrl());
        $packageDetails = $this->getPackageFromPackagist($repositoryMetadata->getRepositoryId());

        $contentCreateStruct->setField('package_id', $packageDetails->packageId);
        $contentCreateStruct->setField('name', $package->getName());
        $contentCreateStruct->setField('description', $this->richTextHelper->getXmlString($packageDetails->description));
        $contentCreateStruct->setField('packagist_url', $package->getUrl());
        $contentCreateStruct->setField('downloads', $packageDetails->packageMetadata->downloads);
        $contentCreateStruct->setField('stars', $packageDetails->packageMetadata->stars);
        $contentCreateStruct->setField('forks', $packageDetails->packageMetadata->forks);
        $contentCreateStruct->setField('updated', $packageDetails->packageMetadata->updateDate);
        $contentCreateStruct->setField('checksum', $packageDetails->checksum);
        $contentCreateStruct->setField('package_category', $this->getTagsFromCategories($package->getCategories()));
        $contentCreateStruct->setField('readme', $packageDetails->readme);

        $locationCreateStruct = $this->locationService->newLocationCreateStruct($this->parentLocationId);

        return $this->contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackage(string $packageName, bool $force = false): Package
    {
        $packageName = trim($packageName);

        /** @var CacheItemInterface $item */
        $item = $this->cacheService->getItem($this->removeReservedCharactersFromPackageName($packageName));

        if ($force || !$item->isHit()) {
            $packageDetails = $this->getPackageFromPackagist($packageName);
            $item->expiresAfter((int) $this->cacheService->getCacheExpirationTime());
            $this->cacheService->save($item->set($packageDetails));

            return $packageDetails;
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageFromPackagist(string $packageName): ?Package
    {
        $packageName = trim($packageName);

        $package = $this->packagistService->getPackageDetails($packageName);

        $repositoryMetadata = new RepositoryMetadata($package->repository);
        $readme = $this->packageRepository->getReadme($repositoryMetadata);

        if ($readme) {
            $crawler = new Crawler($readme);
            $this->domService->removeElementsFromDOM($crawler, ['.anchor', '[data-canonical-src]']);
            $this->domService->setAbsoluteURL($crawler, [
                'host' => $repositoryMetadata->getRepositoryPlatform() === GitLabService::REPOSITORY_PLATFORM_NAME
                    ? $repositoryMetadata->getRepositoryHost() : $package->repository,
                'link' => $this->getRepositoryUrlParts($repositoryMetadata->getRepositoryPlatform()),
            ]);

            $package->readme = $crawler->html();
        }

        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReservedCharactersFromPackageName(string $packageName): string
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '-', $packageName);
    }

    /**
     * @param string $repositoryPlatform
     *
     * @return array
     */
    private function getRepositoryUrlParts(string $repositoryPlatform): array
    {
        return self::REPOSITORY_PLATFORMS[$repositoryPlatform] ?? [];
    }

    /**
     * @param array $categories
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
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
}
