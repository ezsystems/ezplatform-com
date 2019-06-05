<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Helper;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\TagsBundle\API\Repository\TagsService as TagsServiceInterface;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class PackageCategoryListHelper
{
    /** @var \Netgen\TagsBundle\API\Repository\TagsService */
    private $tagsService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /**
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolve
     */
    public function __construct(TagsServiceInterface $tagsService, ConfigResolverInterface $configResolver)
    {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
    }

    /**
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getPackageCategoryList(): array
    {
        $categories = $this->getPackageCategoryTags();
        $choices = [];

        /** @var Tag $category */
        foreach ($categories as $category) {
            $choices[$category->getKeyword()] = $category->id;
        }

        return $choices;
    }

    /**
     * Returns list with package categories.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getPackageCategoryTags(): array
    {
        $tag = $this->tagsService->loadTag(
            $this->configResolver->getParameter('package_categories_parent_tag_id', 'app')
        );

        return $this->tagsService->loadTagChildren($tag);
    }
}
