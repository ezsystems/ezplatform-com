<?php

/**
 * AbstractPackageTestFixture.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Tests\Objects;

use AppBundle\ValueObject\PackageMetadata;
use Packagist\Api\Result\Package\Author;
use Packagist\Api\Result\Package\Downloads;
use Packagist\Api\Result\Package\Maintainer;
use Packagist\Api\Result\Package\Version;

abstract class AbstractPackage
{
    /**
     * @param bool $exclude
     *
     * @return \Packagist\Api\Result\Package\Maintainer[]
     */
    protected function getMaintainers($exclude = true): array
    {
        $maintainer = new Maintainer();
        $maintainer->fromArray([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'homepage' => 'example.com',
        ]);

        if (!$exclude) {
            $excludedMaintainer = new Maintainer();
            $excludedMaintainer->fromArray([
                'name' => 'ezrobot',
                'email' => 'nospam@ez.no',
                'homepage' => 'ez.no',
            ]);
        }

        $maintainers[] = $maintainer;

        if (!$exclude) {
            $maintainers[] = $excludedMaintainer;
        }

        return $maintainers;
    }

    /**
     * @return \Packagist\Api\Result\Package\Author
     */
    protected function getAuthor(): Author
    {
        $author = new Author();
        $author->fromArray([
            'role' => 'author',
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'homepage' => 'example.com',
        ]);

        return $author;
    }

    /**
     * @return \Packagist\Api\Result\Package\Version
     */
    protected function getVersion(): Version
    {
        $version = new Version();
        $version->fromArray([
            'name' => 'dev-master',
            'time' => '2017-11-03T19:51:03+00:00',
            'authors' => [
                $this->getAuthor(),
            ],
        ]);

        return $version;
    }

    /**
     * @return \AppBundle\ValueObject\PackageMetadata
     */
    protected function getPackageMetadata(): PackageMetadata
    {
        $packageMetadata = new PackageMetadata();
        $packageMetadata->downloads = 222;
        $packageMetadata->forks = 3;
        $packageMetadata->stars = 12;
        $packageMetadata->creationDate = \DateTime::createFromFormat(\DateTime::ISO8601, '2017-11-01T19:00:03+00:00');
        $packageMetadata->updateDate = \DateTime::createFromFormat(\DateTime::ISO8601, '2017-11-03T19:51:03+00:00');

        return $packageMetadata;
    }

    /**
     * @return \Packagist\Api\Result\Package\Downloads
     */
    protected function getDownloads(): Downloads
    {
        $downloads = new Downloads();
        $downloads->fromArray([
            'total' => 222,
        ]);

        return $downloads;
    }
}
