<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppConfigExtension extends AbstractExtension implements GlobalsInterface
{
    /** @var int */
    private $releasesFolderLocationId;

    public function __construct(int $releasesFolderLocationId)
    {
        $this->releasesFolderLocationId = $releasesFolderLocationId;
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals(): array
    {
        return [
            'app_releases_folder_location_id' => $this->releasesFolderLocationId
        ];
    }
}
