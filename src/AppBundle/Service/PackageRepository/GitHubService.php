<?php

/**
 * Provides method to call GitHub.com API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\PackageRepository;

use AppBundle\Helper\LoggerTrait;
use AppBundle\ValueObject\RepositoryMetadata;
use Github\Api\Repo;
use Github\Client;

class GitHubService implements PackageRepositoryServiceInterface
{
    use LoggerTrait;

    public const REPOSITORY_PLATFORM_NAME = 'github';
    public const GITHUB_DEFAULT_BRANCH = 'HEAD';
    public const GITHUB_HREF_PART = 'blob';
    public const GITHUB_IMG_PART = 'raw';
    public const GITHUB_URL_PARTS = [
        'href' => self::GITHUB_HREF_PART . '/' . self::GITHUB_DEFAULT_BRANCH,
        'src' => self::GITHUB_IMG_PART . '/' . self::GITHUB_DEFAULT_BRANCH,
    ];

    /** @var \GitHub\Client */
    private $gitHubClient;

    /** @var string */
    private $authenticationToken;

    /**
     * @param \Github\Client $gitHubClient
     * @param string $authenticationToken
     */
    public function __construct(Client $gitHubClient, string $authenticationToken)
    {
        $this->gitHubClient = $gitHubClient;
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string
    {
        try {
            return $this->getPackageRepository()->readme($repositoryMetadata->getUsername(), $repositoryMetadata->getRepositoryName(), $format);
        } catch (\Exception $exception) {
            $this->logError(
                sprintf(
                    'GitHub API Exception: %s | RepositoryId: %s',
                    $exception->getMessage(),
                    $repositoryMetadata->getRepositoryId()
                )
            );
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canGetClientService(RepositoryMetadata $repositoryMetadata): bool
    {
        return $repositoryMetadata->getRepositoryPlatform() === self::REPOSITORY_PLATFORM_NAME;
    }

    /**
     * @return \Github\Api\Repo
     */
    private function getPackageRepository(): Repo
    {
        $this->gitHubClient->authenticate($this->authenticationToken, null, Client::AUTH_URL_TOKEN);

        return $this->gitHubClient->repository();
    }
}
