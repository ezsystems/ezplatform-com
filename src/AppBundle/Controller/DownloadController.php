<?php

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;

class DownloadController extends Controller
{
    /**
     * This method will return all the releases: sub-items of Releases and Betas content objects.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function showFilesAction(ContentView $view)
    {
        $releaseContainerId = $this->container->getParameter('release_container_location_id');
        $betaContainerId = $this->container->getParameter('beta_container_location_id');

        $criteria = array();
        $criteria[] = new Criterion\ParentLocationId($releaseContainerId);
        $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        $criteria[] = new Criterion\ContentTypeIdentifier('release');

        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = array(
            new SortClause\Field('release', 'release_date', Query::SORT_DESC)
        );

        $searchService = $this->getRepository()->getSearchService();
        $subContent = $searchService->findContent($query);
        $releases = array();
        foreach ($subContent->searchHits as $hit) {
            $releases[] = $hit->valueObject;
        }

        $criteria = array();
        $criteria[] = new Criterion\ParentLocationId($betaContainerId);
        $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        $criteria[] = new Criterion\ContentTypeIdentifier('release');

        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = array(
            new SortClause\Field('release', 'release_date', Query::SORT_DESC)
        );

        $searchService = $this->getRepository()->getSearchService();
        $subContent = $searchService->findContent($query);
        $betas = array();
        foreach ($subContent->searchHits as $hit) {
            $betas[] = $hit->valueObject;
        }

        $view->addParameters(
            array(
                'releases' => $releases,
                'betas' => $betas
            )
        );

        return $view;
    }
}
