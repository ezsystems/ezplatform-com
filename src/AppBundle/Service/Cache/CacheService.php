<?php

/**
 * CacheService.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Service\Cache;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class CacheService.
 */
class CacheService implements CacheServiceInterface
{
    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    private $cache;

    /** @var int */
    private $cacheExpirationTime;

    /**
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cacheItemPool
     * @param int $cacheExpirationTime
     */
    public function __construct(TagAwareAdapterInterface $cacheItemPool, int $cacheExpirationTime)
    {
        $this->cache = $cacheItemPool;
        $this->cacheExpirationTime = $cacheExpirationTime;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItem(string $key): CacheItemInterface
    {
        return $this->cache->getItem($key);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->cache->getItems($keys);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function saveCacheItems(iterable $cacheItems): void
    {
        foreach ($cacheItems as $cacheItem) {
            $this->save($cacheItem);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function invalidateTags(array $tags): bool
    {
        return $this->cache->invalidateTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheExpirationTime(): int
    {
        return $this->cacheExpirationTime;
    }
}
