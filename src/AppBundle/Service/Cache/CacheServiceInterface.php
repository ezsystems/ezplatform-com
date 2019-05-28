<?php

/**
 * CacheServiceInterface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Interface CacheServiceInterface.
 */
interface CacheServiceInterface
{
    /**
     * @param string $key
     *
     * @return \Psr\Cache\CacheItemInterface
     */
    public function getItem(string $key): CacheItemInterface;

    /**
     * @param array $keys
     *
     * @return array|\Traversable
     */
    public function getItems(array $keys = []): iterable;

    /**
     * @param iterable $cacheItems
     */
    public function saveCacheItems(iterable $cacheItems): void;

    /**
     * @param \Psr\Cache\CacheItemInterface $item
     *
     * @return bool
     */
    public function save(CacheItemInterface $item): bool;

    /**
     * @param array $tags
     *
     * @return bool
     */
    public function invalidateTags(array $tags): bool;

    /**
     * @return int
     */
    public function getCacheExpirationTime(): int;
}
