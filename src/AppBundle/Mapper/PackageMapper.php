<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Mapper;

use AppBundle\ValueObject\Package;
use AppBundle\ValueObject\PackageMetadata;
use Packagist\Api\Result\Package as PackagistApiPackage;

/**
 * Class PackageMapper.
 */
class PackageMapper
{
    const GITHUB_AVATAR_BASE_URL = 'https://avatars2.githubusercontent.com/';

    /** @var string[] */
    private $excludedMaintainers;

    /**
     * @param array $excludedMaintainers
     */
    public function __construct(array $excludedMaintainers = [])
    {
        $this->excludedMaintainers = $excludedMaintainers;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return \AppBundle\ValueObject\Package
     */
    public function createPackageFromPackagistApiResult(PackagistApiPackage $packagistApiPackage): Package
    {
        $package = new Package();

        $package->packageId = $packagistApiPackage->getName();
        $package->description = $packagistApiPackage->getDescription();
        $package->maintainers = $this->getMaintainers($packagistApiPackage);
        $package->authorAvatarUrl = $this->getAuthorAvatarUrl($packagistApiPackage);
        $package->author = $this->getAuthor($packagistApiPackage);
        $package->repository = $this->getRepository($packagistApiPackage);
        $package->checksum = $this->getChecksum($package);
        $package->packageMetadata = $this->getPackageMetadata($packagistApiPackage);

        return $package;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return \Packagist\Api\Result\Package\Maintainer[]
     */
    private function getMaintainers(PackagistApiPackage $packagistApiPackage): iterable
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
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return string
     */
    private function getAuthorAvatarUrl(PackagistApiPackage $packagistApiPackage): string
    {
        $parsedUrl = parse_url($packagistApiPackage->getRepository());
        $parts = explode('/', $parsedUrl['path']);

        return self::GITHUB_AVATAR_BASE_URL . $parts[1];
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return mixed
     */
    private function getAuthor(PackagistApiPackage $packagistApiPackage)
    {
        $version = $this->getCurrentVersion($packagistApiPackage);
        $authors = $version->getAuthors();

        return reset($authors);
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return string
     */
    private function getRepository(PackagistApiPackage $packagistApiPackage): string
    {
        return preg_replace('/\.git$/', '', $packagistApiPackage->getRepository(), 1);
    }

    /**
     * @param \AppBundle\ValueObject\Package $package
     *
     * @return string
     */
    private function getChecksum(Package $package): string
    {
        $checksum = '';

        foreach (get_object_vars($package) as $key => $field) {
            if (is_string($field) && $key != 'checksum') {
                $checksum .= $field;
            }
        }

        return md5($checksum);
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return PackageMetadata
     */
    private function getPackageMetadata(PackagistApiPackage $packagistApiPackage): PackageMetadata
    {
        $packageMetadata = new PackageMetadata();
        $packageMetadata->downloads = $this->getDownloads($packagistApiPackage);
        $packageMetadata->forks = $this->getForks($packagistApiPackage);
        $packageMetadata->stars = $this->getStars($packagistApiPackage);
        $packageMetadata->updateDate = $this->getUpdateDate($packagistApiPackage);
        $packageMetadata->creationDate = $this->getCreationDate($packagistApiPackage);

        return $packageMetadata;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return int
     */
    private function getDownloads(PackagistApiPackage $packagistApiPackage): int
    {
        $downloads = $packagistApiPackage->getDownloads()->getTotal();

        return (int)$downloads;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return int
     */
    private function getForks(PackagistApiPackage $packagistApiPackage): int
    {
        $forks = $packagistApiPackage->getGithubForks();

        return (int)$forks;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return int
     */
    private function getStars(PackagistApiPackage $packagistApiPackage): int
    {
        $stars = $packagistApiPackage->getGithubStars();

        return (int)$stars;
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return bool|\DateTime
     */
    private function getUpdateDate(PackagistApiPackage $packagistApiPackage)
    {
        $version = $this->getCurrentVersion($packagistApiPackage);

        return \DateTime::createFromFormat(\DateTime::ISO8601, $version->getTime());
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return bool|\DateTime
     */
    private function getCreationDate(PackagistApiPackage $packagistApiPackage)
    {
        return \DateTime::createFromFormat(\DateTime::ISO8601, $packagistApiPackage->getTime());
    }

    /**
     * @param \Packagist\Api\Result\Package $packagistApiPackage
     *
     * @return \Packagist\Api\Result\Package\Version
     */
    private function getCurrentVersion(PackagistApiPackage $packagistApiPackage): PackagistApiPackage\Version
    {
        $versions = $packagistApiPackage->getVersions();

        if (isset($versions['dev-master'])) {
            $key = 'dev-master';
        } else {
            $key = key($versions);
        }

        return $versions[$key];
    }
}
