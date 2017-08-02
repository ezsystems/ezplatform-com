<?php

/**
 * QueryType for Bundle ContentType.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class BundlesQueryType implements QueryType
{
    /**
     * Builds and returns the Query object.
     *
     * @param array $parameters A hash of parameters that will be used to build the Query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    public function getQuery(array $parameters = [])
    {
        $options = [];

        $criteria = [
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE)
        ];

        if (isset($parameters['search']) && !empty($parameters['search'])) {
            $options['query'] = new Query\Criterion\FullText($parameters['search'], [
                'customFields' => [
                    'bundle_id',
                    'name',
                    'description',
                    'packagist_url'
                ]
            ]);
        }

        $options['filter'] = new Query\Criterion\LogicalAnd($criteria);

        if (isset($parameters['order'])) {
            if ($parameters['order'] === 'latestUpdate') {
                $options['sortClauses'] = [new Query\SortClause\Field('bundle', 'updated', Query::SORT_DESC)];
            } elseif ($parameters['order'] === 'stars') {
                $options['sortClauses'] = [new Query\SortClause\Field('bundle', 'stars', Query::SORT_DESC)];
            } elseif ($parameters['order'] === 'downloads') {
                $options['sortClauses'] = [new Query\SortClause\Field('bundle', 'downloads', Query::SORT_DESC)];
            } else {
                $options['sortClauses'] = [new Query\SortClause\DateModified(Query::SORT_DESC)];
            }
        }

        if (isset($parameters['limit'])) {
            $options['limit'] = $parameters['limit'];
        }

        if (isset($parameters['offset'])) {
            $options['offset'] = $parameters['offset'];
        }

        return new LocationQuery($options);
    }

    /**
     * Returns an array listing the parameters supported by the QueryType.
     *
     * @return array
     */
    public function getSupportedParameters()
    {
        return [
            'parent_location_id',
            'limit',
            'offset',
            'order',
        ];
    }

    /**
     * Returns the QueryType name.
     *
     * @return string
     */
    public static function getName()
    {
        return 'AppBundle:Bundles';
    }
}

