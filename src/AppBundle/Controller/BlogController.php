<?php

/**
 * BlogController.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\API\Repository\SearchService;
use AppBundle\QueryType\ChildrenQueryType;

/**
 * Class BlogController.
 */
class BlogController
{
    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    private $templating;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \AppBundle\QueryType\ChildrenQueryType */
    private $childrenQueryType;

    /** @var int */
    private $blogLocationId;

    /** @var int */
    private $blogPostsLimit;

    /**
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\QueryType\ChildrenQueryType $childrenQueryType
     * @param int $blogLocationId
     * @param int $blogPostsLimit
     */
    public function __construct(
        EngineInterface $templating,
        SearchService $searchService,
        ChildrenQueryType $childrenQueryType,
        $blogLocationId,
        $blogPostsLimit
    ) {
        $this->templating = $templating;
        $this->searchService = $searchService;
        $this->childrenQueryType = $childrenQueryType;
        $this->blogLocationId = $blogLocationId;
        $this->blogPostsLimit = $blogPostsLimit;
    }

    /**
     * Renders `blog_post` list for the given $page.
     *
     * @param int $page
     *
     * @return JsonResponse
     *
     * @throws \Twig\Error\Error
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function showBlogPostsAction(int $page): JsonResponse
    {
        $offset = $page * $this->blogPostsLimit - $this->blogPostsLimit;

        $query = $this->childrenQueryType->getQuery([
            'parent_location_id' => $this->blogLocationId,
            'limit' => $this->blogPostsLimit,
            'offset' => $offset,
            'sorting_content_type' => 'blog_post',
            'sorting_field' => 'publication_date',
            'sorting_order' => 'desc',
        ]);
        $searchResults = $this->searchService->findLocations($query);

        $renderedContent = $this->templating->render('@ezdesign/parts/content_list.html.twig', [
            'items' => $searchResults,
            'viewType' => 'line',
            'extraParams' => [
                'page' => $page,
            ],
        ]);

        return new JsonResponse([
            'html' => $renderedContent,
            'showLoadMoreButton' => $searchResults->totalCount > ($offset + count($searchResults->searchHits)),
        ]);
    }

    public function redirectToExternalBlogAction(Request $request)
    {
        $url = 'https://www.ibexa.co' . $request->getPathInfo();
        return new RedirectResponse($url, 301);
    }

}
