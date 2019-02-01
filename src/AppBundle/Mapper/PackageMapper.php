<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Mapper;

use AppBundle\ValueObject\Package;
use Packagist\Api\Result\Package as PackagistApiPackage;

/**
 * Class PackageMapper
 *
 * @package AppBundle\Mapper
 */
class PackageMapper
{
    const GITHUB_AVATAR_BASE_URL = 'https://avatars2.githubusercontent.com/';

    /**
     * @var string[]
     */
    private $excludedMaintainers;

    public function __construct(array $excludedMaintainers = [])
    {
        $this->excludedMaintainers = $excludedMaintainers;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return Package
     */
    public function createPackageFromPackagistApiResult(PackagistApiPackage $packagistApiPackage)
    {
        $package = new Package();

        $package->packageId = $packagistApiPackage->getName();
        $package->description = $packagistApiPackage->getDescription();
        $package->downloads = $this->getDownloads($packagistApiPackage);
        $package->maintainers = $this->getMaintainers($packagistApiPackage);
        $package->authorAvatarUrl = $this->getAuthorAvatarUrl($packagistApiPackage);
        $package->forks = $this->getForks($packagistApiPackage);
        $package->stars = $this->getStars($packagistApiPackage);
        $package->author = $this->getAuthor($packagistApiPackage);
        $package->updateDate = $this->getUpdateDate($packagistApiPackage);
        $package->creationDate = $this->getCreationDate($packagistApiPackage);
        $package->repository = $packagistApiPackage->getRepository();
        $package->checksum = $this->getChecksum($package);

        return $package;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return PackagistApiPackage\Maintainer[]
     */
    private function getMaintainers(PackagistApiPackage $packagistApiPackage)
    {
        $maintainers = [];
        foreach ($packagistApiPackage->getMaintainers() as $key => $value) {
            if (!in_array($value->getName(), $this->excludedMaintainers)) {
                $maintainers[$key] = $value;
            }
        }

        return $maintainers;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return string
     */
    private function getAuthorAvatarUrl(PackagistApiPackage $packagistApiPackage)
    {
        $parsedUrl = parse_url($packagistApiPackage->getRepository());
        $parts = explode('/', $parsedUrl['path']);

        return self::GITHUB_AVATAR_BASE_URL.$parts[1];
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return int
     */
    private function getDownloads(PackagistApiPackage $packagistApiPackage)
    {
        $downloads = $packagistApiPackage->getDownloads()->getTotal();

        return (int)$downloads;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return int
     */
    private function getForks(PackagistApiPackage $packagistApiPackage)
    {
        $forks = $packagistApiPackage->getGithubForks();

        return (int)$forks;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return int
     */
    private function getStars(PackagistApiPackage $packagistApiPackage)
    {
        $stars = $packagistApiPackage->getGithubStars();

        return (int)$stars;
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return PackagistApiPackage\Author
     */
    private function getAuthor(PackagistApiPackage $packagistApiPackage)
    {
        $version = $this->getCurrentVersion($packagistApiPackage);
        $authors = $version->getAuthors();

        return reset($authors);
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return bool|\DateTime
     */
    private function getUpdateDate(PackagistApiPackage $packagistApiPackage)
    {
        $version = $this->getCurrentVersion($packagistApiPackage);

        return \DateTime::createFromFormat(\DateTime::ISO8601, $version->getTime());
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return PackagistApiPackage\Version
     */
    private function getCurrentVersion(PackagistApiPackage $packagistApiPackage)
    {
        $versions = $packagistApiPackage->getVersions();
        if (isset($versions['dev-master'])) {
            $key = 'dev-master';
        } else {
            $key = key($versions);
        }

        return $versions[$key];
    }

    /**
     * @param Package $package
     * @return string
     */
    private function getChecksum(Package $package)
    {
        $checksum = '';
        foreach (get_object_vars($package) as $key => $field) {
            if (is_string($field) && $key != 'checksum') $checksum .= $field;
        }

        return md5($checksum);
    }

    /**
     * @param PackagistApiPackage $packagistApiPackage
     * @return bool|\DateTime
     */
    private function getCreationDate(PackagistApiPackage $packagistApiPackage)
    {
        return \DateTime::createFromFormat(\DateTime::ISO8601, $packagistApiPackage->getTime());
    }
}
