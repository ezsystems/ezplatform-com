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

    public function __construct(string $repositoryId)
    {
        $this->repositoryId = $this->splitRepositoryId($repositoryId);
        $this->username = $this->getUsernameFromRepositoryId();
        $this->repositoryName = $this->getRepositoryNameFromRepositoryId();
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
     * @param string $repositoryId
     *
     * @return array
     */
    private function splitRepositoryId(string $repositoryId): array
    {
        return explode(self::DEFAULT_DELIMITER, $repositoryId);
    }

    /**
     * @return string|null
     */
    private function getUsernameFromRepositoryId(): ?string
    {
        return isset($this->repositoryId[count($this->repositoryId) - 2]) ? $this->repositoryId[count($this->repositoryId) - 2] : '';
    }

    /**
     * @return string|null
     */
    private function getRepositoryNameFromRepositoryId(): ?string
    {
        return end($this->repositoryId);
    }
}
