<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\ValueObject;

class Package
{
    /**
     * @var string
     */
    public $packageId;

    /**
     * @var \Packagist\Api\Result\Package\Maintainer[]
     */
    public $maintainers;

    /**
     * @var string
     */
    public $authorAvatarUrl;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $downloads;

    /**
     * @var int
     */
    public $forks;

    /**
     * @var int
     */
    public $stars;

    /**
     * @var \DateTime
     */
    public $creationDate;

    /**
     * @var \DateTime
     */
    public $updateDate;

    /**
     * @var \Packagist\Api\Result\Package\Author
     */
    public $author;

    /**
     * @var string
     */
    public $repository;

    /**
     * @var string
     */
    public $readme;

    /**
     * @var string
     */
    public $checksum;
}
