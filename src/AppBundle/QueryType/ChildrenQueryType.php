<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class ChildrenQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $options = [];

        $criteria = [
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
        ];

        if (isset($parameters['excluded_content_types'])) {
            $criteria[] = new Query\Criterion\LogicalNot(
                new Query\Criterion\ContentTypeIdentifier($parameters['excluded_content_types'])
            );
        }

        $options['filter'] = new Query\Criterion\LogicalAnd($criteria);

        $sortClauses = new Query\SortClause\Location\Priority(Query::SORT_ASC);
        if (isset($parameters['sorting_content_type']) &&
            isset($parameters['sorting_field']) &&
            isset($parameters['sorting_order'])) {
            $sortClauses = new Query\SortClause\Field(
                $parameters['sorting_content_type'],
                $parameters['sorting_field'],
                $parameters['sorting_order'] == 'desc' ? Query::SORT_DESC : Query::SORT_ASC
            );
        }

        $options['sortClauses'] = [$sortClauses];

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
     * Optional parameters are:
     *      - limit (int)
     *      - offset (int)
     *      - excluded_content_types (array|string)
     *
     * Additional parameters for sorting options (all of them are required):
     *      - sorting_content_type (string)
     *      - sorting_field (string)
     *      - sorting_order (asc|desc)
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
