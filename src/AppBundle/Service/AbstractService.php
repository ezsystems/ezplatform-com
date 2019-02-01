<?php

/**
 * AbstractService - provides interfaces to manage Content items
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Service;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;

/**
 * Class AbstractService
 *
 * @package AppBundle\Service
 */
abstract class AbstractService
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    public function __construct(
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }
}
