<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\API\Repository\SearchService;
use AppBundle\QueryType\ChildrenQueryType;

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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function showBlogPostsAction($page)
    {
        $offset = $page * $this->blogPostsLimit - $this->blogPostsLimit;

        $query = $this->childrenQueryType->getQuery([
            'parent_location_id' => $this->blogLocationId,
            'limit' => $this->blogPostsLimit,
            'offset' => $offset,
        ]);
        $searchResults = $this->searchService->findLocations($query);

        $renderedContent = $this->templating->render('parts/content_list.html.twig', [
            'items' => $searchResults,
            'viewType' => 'line',
            'extraParams' => [
                'page' => $page,
            ],
        ]);

        $showMoreButton = $searchResults->totalCount > ($offset + count($searchResults->searchHits)) ? true : false;

        return new JsonResponse([
            'html' => $renderedContent,
            'showLoadMoreButton' => $showMoreButton,
        ]);
    }
}
