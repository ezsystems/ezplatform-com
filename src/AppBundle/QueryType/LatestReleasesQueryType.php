<?php

/**
 * LatestReleasesQueryType.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

/**
 * Class LatestReleasesQueryType.
 */
class LatestReleasesQueryType implements QueryType
{
    /**
     * @param array $parameters
     *
     * @return LocationQuery|Query
     */
    public function getQuery(array $parameters = []): LocationQuery
    {
        $criteria = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ParentLocationId($parameters['parent_location_id']),
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\ContentTypeIdentifier('release'),
        ]);

        $options = [
            'filter' => $criteria,
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
