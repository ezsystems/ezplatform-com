<?php

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

class LatestReleasesQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        $criteria = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\ContentTypeIdentifier('release'),
        ]);

        $options = [
            'filter' => $criteria
        ];

        return new LocationQuery($options);
    }

    public static function getName()
    {
        return 'AppBundle:LatestReleases';
    }

    public function getSupportedParameters()
    {
        return ['parent_location_id'];
    }
}
