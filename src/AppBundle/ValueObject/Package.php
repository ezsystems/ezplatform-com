<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\ValueObject;

/**
 * Class Package.
 */
class Package
{
    /** @var string */
    public $packageId;

    /** @var \Packagist\Api\Result\Package\Maintainer[] */
    public $maintainers;

    /** @var string */
    public $authorAvatarUrl;

    /** @var string */
    public $description;

    /** @var \AppBundle\ValueObject\PackageMetadata */
    public $packageMetadata;

    /** @var \Packagist\Api\Result\Package\Author */
    public $author;

    /** @var string */
    public $repository;

    /** @var string */
    public $readme;

    /** @var string */
    public $checksum;
}
