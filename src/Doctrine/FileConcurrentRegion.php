<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Cache\CacheEntry;
use Doctrine\ORM\Cache\CacheKey;
use Doctrine\ORM\Cache\CollectionCacheEntry;
use Doctrine\ORM\Cache\ConcurrentRegion;
use Doctrine\ORM\Cache\Lock;

class FileConcurrentRegion implements ConcurrentRegion
{
    public function lock(CacheKey $key)
    {
        // TODO: Implement lock() method.
    }

    public function unlock(CacheKey $key, Lock $lock)
    {
        // TODO: Implement unlock() method.
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function contains(CacheKey $key)
    {
        // TODO: Implement contains() method.
    }

    public function get(CacheKey $key)
    {
        // TODO: Implement get() method.
    }

    public function put(CacheKey $key, CacheEntry $entry, ?Lock $lock = null)
    {
        // TODO: Implement put() method.
    }

    public function evict(CacheKey $key)
    {
        // TODO: Implement evict() method.
    }

    public function evictAll()
    {
        // TODO: Implement evictAll() method.
    }

    public function getMultiple(CollectionCacheEntry $collection)
    {
        // TODO: Implement getMultiple() method.
    }
}
