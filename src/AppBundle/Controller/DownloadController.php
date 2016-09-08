<?php

namespace AppBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\API\Repository\SearchService;
use AppBundle\QueryType\LatestReleasesQueryType;

class DownloadController
{
    /** @var int */
    private $releaseContainerLocationid;

    /** @var int */
    private $betaContainerLocationId;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \AppBundle\QueryType\LatestReleasesQueryType */
    private $latestReleasesQueryType;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\QueryType\LatestReleasesQueryType $latestReleasesQueryType
     * @param int $releaseContainerLocationid
     * @param int $betaContainerLocationId
     */
    public function __construct(
        SearchService $searchService,
        LatestReleasesQueryType $latestReleasesQueryType,
        $releaseContainerLocationid,
        $betaContainerLocationId
    ) {
        $this->searchService = $searchService;
        $this->latestReleasesQueryType = $latestReleasesQueryType;
        $this->releaseContainerLocationid = $releaseContainerLocationid;
        $this->betaContainerLocationId = $betaContainerLocationId;
    }

    /**
     * Renders view for the Download section including all the eZ Platform releases.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function show(ContentView $view)
    {
        $view->addParameters([
            'releases' => $this->getSearchResults($this->releaseContainerLocationid),
            'betas' => $this->getSearchResults($this->betaContainerLocationId),
        ]);

        return $view;
    }

    /**
     * Returns releases search results for the given $parentLocationId.
     *
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    private function getSearchResults($parentLocationId)
    {
        $query = $this->latestReleasesQueryType->getQuery([
            'parent_location_id' => $parentLocationId,
        ]);
        $searchResults = $this->searchService->findContent($query);

        $results = [];
        foreach ($searchResults->searchHits as $hit) {
            $results[] = $hit->valueObject;
        }

        return $results;
    }
}
