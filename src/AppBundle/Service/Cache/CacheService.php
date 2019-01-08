<?php

/**
 * CacheService
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\Service\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CacheService
 * @package AppBundle\Service\Cache
 */
class CacheService implements CacheServiceInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var int
     */
    private $cacheExpirationTime;

    public function __construct(CacheItemPoolInterface $cacheItemPool, int $cacheExpirationTime)
    {
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheExpirationTime = $cacheExpirationTime;
    }

    /**
     * @param string $key
     *
     * @return \Psr\Cache\CacheItemInterface
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItem(string $key): CacheItemInterface
    {
        return $this->cacheItemPool->getItem($key);
    }

    /**
     * @param array $keys
     *
     * @return array|\Traversable
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->cacheItemPool->getItems($keys);
    }

    /**
     * @param $item
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->cacheItemPool->save($item);
    }

    /**
     * @return int
     */
    public function getCacheExpirationTime(): int
    {
        return $this->cacheExpirationTime;
    }
}
