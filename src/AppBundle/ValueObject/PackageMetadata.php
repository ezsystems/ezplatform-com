<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\ValueObject;

class PackageMetadata
{
    /** @var int */
    public $downloads;

    /** @var int */
    public $forks;

    /** @var int */
    public $stars;

    /** @var \DateTime */
    public $creationDate;

    /** @var \DateTime */
    public $updateDate;
}
