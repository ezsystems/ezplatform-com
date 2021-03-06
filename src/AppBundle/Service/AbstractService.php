<?php

/**
 * AbstractService - provides interfaces to manage Content items.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service;

use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;

/**
 * Class AbstractService.
 */
abstract class AbstractService
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    protected $userService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        UserServiceInterface $userService,
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }
}
