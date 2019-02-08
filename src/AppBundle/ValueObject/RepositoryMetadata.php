<?php

/**
 * RepositoryMetadata
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\ValueObject;

/**
 * Class RepositoryMetadata
 * @package AppBundle\ValueObject
 */
final class RepositoryMetadata
{
    private const DEFAULT_DELIMITER = '/';

    private const ALLOWED_REPOSITORY_PLATFORMS = [
        'github',
        'gitlab'
    ];

    /**
     * @var array
     */
    private $splitUrl = [];

    /**
     * @var string
     */
    private $repositoryId = '';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var string
     */
    private $repositoryPlatform;

    public function __construct(string $repositoryUrl)
    {
        $this->splitUrl = $this->splitRepositoryUrl($repositoryUrl);
        $this->username = $this->getUsernameFromRepositoryUrl();
        $this->repositoryName = $this->getRepositoryNameFromRepositoryUrl();
        $this->repositoryId = $this->getRepositoryId();
        $this->repositoryPlatform = $this->getRepositoryPlatformFromRepositoryUrl();
    }

    /**
     * @return string
     */
    public function getRepositoryId()
    {
        return $this->username . self::DEFAULT_DELIMITER . $this->repositoryName;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * @return string
     */
    public function getRepositoryPlatform(): string
    {
        return $this->repositoryPlatform;
    }

    /**
     * @param string $repositoryId
     *
     * @return array
     */
    private function splitRepositoryUrl(string $repositoryId): array
    {
        return explode(self::DEFAULT_DELIMITER, $repositoryId);
    }

    /**
     * @return string|null
     */
    private function getUsernameFromRepositoryUrl(): ?string
    {
        return $this->splitUrl[count($this->splitUrl) - 2] ?? '';
    }

    /**
     * @return string|null
     */
    private function getRepositoryNameFromRepositoryUrl(): ?string
    {
        return end($this->splitUrl);
    }

    /**
     * @return string|null
     */
    private function getRepositoryPlatformFromRepositoryUrl(): ?string
    {
        $platformDomain = $this->splitUrl[count($this->splitUrl) - 3] ?? '';
        $platform = explode('.', $platformDomain)[0] ?? '';

        return in_array(
            $platform,
                self::ALLOWED_REPOSITORY_PLATFORMS
            ) ? $platform : '';
    }
}
