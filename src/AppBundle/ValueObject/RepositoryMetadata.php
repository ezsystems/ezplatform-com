<?php

/**
 * RepositoryMetadata.
 *
 * Provides method to call Packagist.org API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\ValueObject;

use GuzzleHttp\Psr7\Uri;

final class RepositoryMetadata
{
    private const DEFAULT_DELIMITER = '/';

    private const ALLOWED_REPOSITORY_PLATFORMS = [
        'github',
        'gitlab',
    ];

    /** @var \Psr\Http\Message\UriInterface */
    private $uri;

    /** @var string */
    private $repositoryId = '';

    /** @var string */
    private $username;

    /** @var string */
    private $repositoryName;

    /** @var string */
    private $repositoryPlatform;

    /** @var string */
    private $repositoryHost;

    /**
     * @param string $repositoryUrl
     */
    public function __construct(string $repositoryUrl)
    {
        $this->uri = new Uri($repositoryUrl);
        $this->username = $this->getUsernameFromRepositoryUrl();
        $this->repositoryName = $this->getRepositoryNameFromRepositoryUrl();
        $this->repositoryId = $this->getRepositoryId();
        $this->repositoryPlatform = $this->getRepositoryPlatformFromRepositoryUrl();
        $this->repositoryHost = $this->getRepositoryHost();
    }

    /**
     * @return string
     */
    public function getRepositoryId(): string
    {
        return $this->getUsername() . self::DEFAULT_DELIMITER . $this->getRepositoryName();
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRepositoryName(): ?string
    {
        return $this->repositoryName;
    }

    /**
     * @return string
     */
    public function getRepositoryPlatform(): ?string
    {
        return $this->repositoryPlatform;
    }

    /**
     * @return string|null
     */
    public function getRepositoryHost(): string
    {
        return $this->uri->getScheme() . '://' . $this->uri->getHost();
    }

    /**
     * @return string[]
     */
    private function getPathParts(): array
    {
        $pathParts = explode(self::DEFAULT_DELIMITER, $this->uri->getPath());

        return array_values(
            array_filter($pathParts, function (string $pathPart) {
                return !empty($pathPart);
            })
        );
    }

    /**
     * @return string|null
     */
    private function getUsernameFromRepositoryUrl(): ?string
    {
        $pathParts = $this->getPathParts();

        return $pathParts[count($pathParts) - 2] ?? null;
    }

    /**
     * @return string|null
     */
    private function getRepositoryNameFromRepositoryUrl(): ?string
    {
        $pathParts = $this->getPathParts();

        return end($pathParts);
    }

    /**
     * @return string|null
     */
    private function getRepositoryPlatformFromRepositoryUrl(): ?string
    {
        $platform = explode('.', $this->uri->getHost())[0] ?? '';

        return in_array(
            $platform,
                self::ALLOWED_REPOSITORY_PLATFORMS
            ) ? $platform : null;
    }
}
