<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Service\Cache\CacheServiceInterface;
use AppBundle\Service\Package\PackageServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Component\Console\Command\Command;

abstract class AbstractUpdatePackageCommand extends Command
{
    private const CONTENT_TYPE_IDENTIFIER = 'package';

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    protected $userService;

    /** @var \AppBundle\Service\Package\PackageServiceInterface */
    protected $packageService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \AppBundle\Service\Cache\CacheServiceInterface */
    protected $cacheService;

    /** @var int */
    private $adminId;

    /** @var int */
    private $packagesLocationId;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \AppBundle\Service\Package\PackageServiceInterface $packageService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\Service\Cache\CacheServiceInterface $cacheService
     * @param int $adminId
     * @param int $packagesLocationId
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        PackageServiceInterface $packageService,
        ContentServiceInterface $contentService,
        SearchServiceInterface $searchService,
        CacheServiceInterface $cacheService,
        int $adminId,
        int $packagesLocationId
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->packageService = $packageService;
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->cacheService = $cacheService;
        $this->adminId = $adminId;
        $this->packagesLocationId = $packagesLocationId;

        parent::__construct();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function getPackages(): array
    {
        return $this->searchService->findContent($this->getQuery($this->packagesLocationId))->searchHits;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function setPermissionResolver(): void
    {
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUser($this->adminId)
        );
    }

    /**
     * @param array $invalidateTags
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     *
     * @return array
     */
    protected function addPackageToInvalidateTag(array $invalidateTags, ValueObject $object): array
    {
        $invalidateTags[] = 'content-' . $object->versionInfo->contentInfo->id;
        $invalidateTags[] = 'location-' . $object->versionInfo->contentInfo->mainLocationId;

        return $invalidateTags;
    }

    /**
     * @param int $packageLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    private function getQuery(int $packageLocationId): Query
    {
        $query = new Query();
        $criterion = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($packageLocationId),
            new Query\Criterion\ContentTypeIdentifier(self::CONTENT_TYPE_IDENTIFIER),
        ]);

        $query->filter = $criterion;
        $query->limit = 1000;

        return $query;
    }
}
