<?php

/**
 * GitLabServiceProvider
 *
 * Provides method to call GitLab.com API.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\GitLab;

use AppBundle\Service\PackageRepository\PackageRepositoryServiceProviderInterface;
use AppBundle\ValueObject\RepositoryMetadata;
use Gitlab\Client;
use Gitlab\HttpClient\Message\ResponseMediator;

/**
 * Class GitLabServiceProvider
 *
 * @package AppBundle\Service\GitLab
 */
class GitLabServiceProvider implements PackageRepositoryServiceProviderInterface
{
    private const REPOSITORY_PLATFORM_NAME = 'gitlab';
    private const README_FILE_PATH = 'README.md';
    private const README_REF = 'master';
    private const MARKDOWN_API_ENDPOINT = 'https://gitlab.com/api/v4/markdown';
    const GITLAB_HREF_PART = 'blob';
    const GITLAB_IMG_PART = 'raw';

    const GITLAB_URL_PARTS = [
        'href' => self::GITLAB_HREF_PART,
        'src' => self::GITLAB_IMG_PART
    ];

    /**
     * @var \Gitlab\Client
     */
    private $gitLabClient;

    public function __construct(Client $gitLabClient)
    {
        $this->gitLabClient = $gitLabClient;
    }

    /**
     * @param RepositoryMetadata $repositoryMetadata
     * @param string $format
     *
     * @return string|null
     *
     * @throws \Http\Client\Exception
     */
    public function getReadme(RepositoryMetadata $repositoryMetadata, string $format = 'html'): ?string
    {
        try {
            $repositoryId = $repositoryMetadata->getRepositoryId();
            $rawReadme = $this->getRawReadme($repositoryId);

            $headers = [
                'Content-Type' => 'application/json'
            ];

            $body = json_encode([
                'text' => $rawReadme,
                'gfm' => true,
                'project' => $repositoryId
            ]);

            return $this->getReadmeAsHtml($headers, $body);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param RepositoryMetadata $repositoryMetadata
     *
     * @return bool
     */
    public function canGetClientProvider(RepositoryMetadata $repositoryMetadata): bool
    {
        return $repositoryMetadata->getRepositoryPlatform() === self::REPOSITORY_PLATFORM_NAME;
    }

    /**
     * @param string $repositoryId
     *
     * @return string|null
     */
    private function getRawReadme(string $repositoryId): ?string
    {
        return $this->gitLabClient
                    ->repositoryFiles()
                    ->getRawFile(
                        $repositoryId,
                        self::README_FILE_PATH,
                        self::README_REF
                    );
    }

    /**
     * @param array $headers
     * @param string $body
     *
     * @return string|null
     *
     * @throws \Http\Client\Exception
     */
    private function getReadmeAsHtml(array $headers = [], string $body = ''): ?string
    {
        $response = ResponseMediator::getContent(
            $this->gitLabClient
                ->getHttpClient()
                ->post(
                    self::MARKDOWN_API_ENDPOINT,
                    $headers,
                    $body
                )
        );

        return $response['html'] ?? '';
    }
}
