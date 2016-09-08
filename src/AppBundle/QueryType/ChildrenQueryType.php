<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class ChildrenQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $criteria = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\LogicalNot(
                new Query\Criterion\ContentTypeIdentifier($parameters['excluded_content_types'])
            ),
        ]);

        $options = [
            'filter' => $criteria,
            'sortClauses' => [
                new Query\SortClause\Location\Priority(Query::SORT_ASC),
            ],
        ];

        return new LocationQuery($options);
    }

    public static function getName()
    {
        return 'AppBundle:Children';
    }

    public function getSupportedParameters()
    {
        return [
            'parent_location_id',
            'excluded_content_types',
        ];
    }
}
