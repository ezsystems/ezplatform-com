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
    /** @var array */
    private $configParameters;

    /**.
     * @param array $configParameters
     */
    public function __construct(array $configParameters)
    {
        $this->configParameters = $configParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals(): array
    {
        return [
            'releaseFolderLocations' => [
                $this->configParameters['releases_folder_location_id'] ?? null,
                $this->configParameters['betas_folder_location_id'] ?? null,
            ],
        ];
    }
}
