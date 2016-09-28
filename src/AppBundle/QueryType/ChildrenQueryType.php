<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class ChildrenQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $criteria = [
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
        ];

        if (isset($parameters['excluded_content_types'])) {
            $criteria[] = new Query\Criterion\LogicalNot(
                new Query\Criterion\ContentTypeIdentifier($parameters['excluded_content_types'])
            );
        }

        $options = [
            'filter' => new Query\Criterion\LogicalAnd($criteria),
            'sortClauses' => [
                new Query\SortClause\Location\Priority(Query::SORT_ASC),
            ],
        ];

        if (isset($parameters['limit'])) {
            $options['limit'] = $parameters['limit'];
        }

        if (isset($parameters['offset'])) {
            $options['offset'] = $parameters['offset'];
        }

        return new LocationQuery($options);
    }

    public static function getName()
    {
        return 'AppBundle:Children';
    }

    /**
     * Returns array of required parameters.
     *
     * Optional parameters are: limit, offset, excluded_content_types
     *
     * @return array
     */
    public function getSupportedParameters()
    {
        return [
            'parent_location_id',
        ];
    }
}
