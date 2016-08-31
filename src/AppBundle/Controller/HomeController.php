<?php

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class HomeController extends Controller
{

    /**
     * Displays the list of sub-items of the root location.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction()
    {
        $root = $this->getRootLocation();

        $criteria = array();
        $criteria[] = new Criterion\ParentLocationId($root->id);
        $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        $criteria[] = new Criterion\LogicalNot(new Criterion\ContentTypeIdentifier('layout'));

        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = array(
            new SortClause\Location\Priority(Query::SORT_ASC),
        );

        $searchService = $this->getRepository()->getSearchService();
        $subContent = $searchService->findLocations($query);
        $items = [];
        foreach ($subContent->searchHits as $hit) {
            $items[] = $hit->valueObject;
        }

        return $this->get('ez_content')->viewContent(
            $root->contentInfo->id,
            'full',
            true,
            ['items' => $items]
        );
    }
}
