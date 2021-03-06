<?php
/**
 * This file provides interoperability with DeviceDetector's not fully compatible caching mechanism.
 *
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Dungeonworx\DeviceDetector;

use DeviceDetector\Cache\Cache;

/**
 * Class LaravelCacheBridge
 *
 * @package Dungeonworx\DeviceDetector
 */
class LaravelCacheBridge implements Cache
{
    /**
     * Contains the cache repository instance currently in use by the Laravel application.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * Contains the configuration instance from Laravel.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * LaravelCacheBridge constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config The configuration repository from Laravel.
     * @param \Illuminate\Contracts\Cache\Repository  $cache  The cache repository from Laravel.
     */
    public function __construct(\Illuminate\Contracts\Config\Repository $config, \Illuminate\Contracts\Cache\Repository $cache)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * Hashes a given key into a format for DeviceDetector safe storage.
     *
     * @param string $key The key to be hashed into DeviceDetector format.
     *
     * @return string
     */
    private function hashKey($key)
    {
        return sprintf('%s:%s', $this->config->get('device_detector.cache_prefix'), md5($key));
    }

    /**
     * Returns an item from the cache.
     *
     * @param string $id The id to fetch from the cache.
     *
     * @return mixed
     */
    public function fetch($id)
    {
        return $this->cache->get($this->hashKey($id), false);
    }

    /**
     * Returns if a cached item exists or not.
     *
     * @param string $id The id to fetch from the cache.
     *
     * @return bool
     */
    public function contains($id)
    {
        return $this->cache->has($this->hashKey($id));
    }

    /**
     * Stores data into the cache.
     *
     * @param string $id       The id to save into the cache.
     * @param mixed  $data     The data to save into the cache.
     * @param int    $lifeTime The TTL for the data to remain in the cache.
     *
     * @return void
     */
    public function save($id, $data, $lifeTime = 0)
    {
        if ($lifeTime >= 1) {
            $this->cache->put($this->hashKey($id), $data, $lifeTime);
        } else {
            $this->cache->forever($this->hashKey($id), $data);
        }
    }

    /**
     * Deletes a specific item from the cache.
     *
     * @param string $id The id to delete from the cache.
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->cache->forget($this->hashKey($id));
    }

    /**
     * Deletes all cache tagged with device_detector.
     *
     * @return mixed
     */
    public function flushAll()
    {
        return false;
    }
}
