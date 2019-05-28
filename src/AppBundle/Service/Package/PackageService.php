<?php

/**
 * PackageService.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Package;

use AppBundle\Helper\RichTextHelper;
use AppBundle\Service\AbstractService;
use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\DOM\DOMServiceInterface;
use AppBundle\Service\GitHub\GitHubServiceProvider;
use AppBundle\Service\GitLab\GitLabServiceProvider;
use AppBundle\Service\PackageRepository\PackageRepositoryProviderStrategy;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
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

class PackageService extends AbstractService implements PackageServiceInterface
{
    const CONTENT_TYPE_NAME = 'package';
    const DEFAULT_LANG_CODE = 'eng-GB';
    private const REPOSITORY_PLATFORMS = [
        'github' => GitHubServiceProvider::GITHUB_URL_PARTS,
        'gitlab' => GitLabServiceProvider::GITLAB_URL_PARTS,
    ];

    /** @var \AppBundle\Service\Packagist\PackagistServiceProviderInterface */
    private $packagistServiceProvider;

    /** @var \AppBundle\Service\PackageRepository\PackageRepositoryProviderStrategy */
    private $packageRepository;

    /** @var \AppBundle\Service\Cache\CacheServiceInterface */
    private $cacheService;

    /** @var \AppBundle\Service\DOM\DOMServiceInterface */
    private $domService;

    /** @var \Netgen\TagsBundle\API\Repository\TagsService */
    private $tagsService;

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
     * @param \AppBundle\Service\Packagist\PackagistServiceProviderInterface $packagistServiceProvider
     * @param \AppBundle\Service\PackageRepository\PackageRepositoryProviderStrategy $packageRepository
     * @param \AppBundle\Service\Cache\CacheServiceInterface $cacheService
     * @param \AppBundle\Service\DOM\DOMServiceInterface $domService
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \AppBundle\Helper\RichTextHelper $richTextHelper
     * @param int $parentLocationId
     * @param int $packageContributorId
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        PackagistServiceProviderInterface $packagistServiceProvider,
        PackageRepositoryProviderStrategy $packageRepository,
        CacheServiceInterface $cacheService,
        DOMServiceInterface $domService,
        TagsServiceInterface $tagsService,
        RichTextHelper $richTextHelper,
        int $parentLocationId,
        int $packageContributorId
    ) {
        $this->packagistServiceProvider = $packagistServiceProvider;
        $this->packageRepository = $packageRepository;
        $this->cacheService = $cacheService;
        $this->domService = $domService;
        $this->tagsService = $tagsService;
        $this->richTextHelper = $richTextHelper;
        $this->parentLocationId = $parentLocationId;
        $this->packageContributorId = $packageContributorId;

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
    public function addPackage(array $formData): Content
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(self::CONTENT_TYPE_NAME);
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, self::DEFAULT_LANG_CODE);

        $packageUrl = $formData['url'] ?? '';
        $packageName = $formData['name'] ?? '';
        $packageCategories = $formData['categories'] ?? [];

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser($this->packageContributorId)
        );

        $repositoryMetadata = new RepositoryMetadata($packageUrl);
        $packageDetails = $this->getPackageFromPackagist($repositoryMetadata->getRepositoryId());

        $contentCreateStruct->setField('package_id', $packageDetails->packageId);
        $contentCreateStruct->setField('name', $packageName);
        $contentCreateStruct->setField('description', $this->richTextHelper->getXmlString($packageDetails->description));
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

        $package = $this->packagistServiceProvider->getPackageDetails($packageName);

        $repositoryMetadata = new RepositoryMetadata($package->repository);
        $readme = $this->packageRepository->getReadme($repositoryMetadata);

        if ($readme) {
            $crawler = new Crawler($readme);
            $this->domService->removeElementsFromDOM($crawler, ['.anchor', '[data-canonical-src]']);
            $this->domService->setAbsoluteURL($crawler, [
                'repository' => $package->repository,
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
