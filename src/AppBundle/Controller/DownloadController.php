<?php

/**
 * DownloadController.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use AppBundle\QueryType\LatestReleasesQueryType;

/**
 * Class DownloadController.
 */
class DownloadController
{
    /** @var int */
    private $releaseContainerLocationId;

    /** @var int */
    private $betaContainerLocationId;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \AppBundle\QueryType\LatestReleasesQueryType */
    private $latestReleasesQueryType;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\QueryType\LatestReleasesQueryType $latestReleasesQueryType
     * @param int $releaseContainerLocationId
     * @param int $betaContainerLocationId
     */
    public function __construct(
        SearchServiceInterface $searchService,
        LatestReleasesQueryType $latestReleasesQueryType,
        $releaseContainerLocationId,
        $betaContainerLocationId
    ) {
        $this->searchService = $searchService;
        $this->latestReleasesQueryType = $latestReleasesQueryType;
        $this->releaseContainerLocationId = $releaseContainerLocationId;
        $this->betaContainerLocationId = $betaContainerLocationId;
    }

    /**
     * Renders view for the Download section including all the eZ Platform releases.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function showAction(ContentView $view)
    {
        $view->addParameters([
            'releases' => $this->getSearchResults($this->releaseContainerLocationId),
            'betas' => $this->getSearchResults($this->betaContainerLocationId),
        ]);

        return $view;
    }

    /**
     * Returns releases search results for the given $parentLocationId.
     *
     * @param $parentLocationId
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function getSearchResults($parentLocationId): array
    {
        $query = $this->latestReleasesQueryType->getQuery([
            'parent_location_id' => $parentLocationId,
        ]);
        $searchResults = $this->searchService->findContent($query);

        $results = [];

        foreach ($searchResults->searchHits as $hit) {
            $results[$hit->valueObject->getFieldValue('release_version')->__toString()] = $hit->valueObject;
        }

        uksort($results, 'version_compare');

        return array_reverse($results);
    }
}
