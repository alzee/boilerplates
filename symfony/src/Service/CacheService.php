<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @author Al Zee <z@alz.ee>
 * @version
 * @todo
 */

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CacheService
{
    private string $prefix;

    public function __construct(
        #[Autowire('@app.cache')] 
        private CacheInterface $cache
    ) {
        $this->prefix = $_ENV['CACHE_PREFIX'] ?? 'app';
    }

    /**
     * Get a cached value or compute it using the callback
     */
    public function get(string $key, callable $callback, int $ttl = 0)
    {
        $fullKey = $this->prefix . '-' . $key;
        return $this->cache->get($fullKey, function(ItemInterface $item) use ($callback, $ttl) {
            if ($ttl > 0) {
                $item->expiresAfter($ttl);
            }
            return $callback();
        });
    }

    public function set(string $key, $value, int $ttl = 0)
    {
        $fullKey = $this->prefix . '-' . $key;
        $item = $this->cache->getItem($fullKey);
        $item->set($value);

        if ($ttl > 0) {
            $item->expiresAfter($ttl);
        }

        $this->cache->save($item);
    }

    /**
     * Check if a cache key exists
     */
    public function has(string $key): bool
    {
        $fullKey = $this->prefix . '-' . $key;
        return $this->cache->hasItem($fullKey);
    }

    /**
     * Delete a specific cache key
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->prefix . '-' . $key;
        return $this->cache->delete($fullKey);
    }

    /**
     * Clear all cache items
     */
    public function clear(): bool
    {
        try {
            return $this->cache->clear();
        } catch (\Exception $e) {
            error_log('Cache clear failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache and warm it with the provided callback
     */
    public function clearAndWarm(string $key, callable $warmCallback): void
    {
        $this->delete($key);
        $this->warm($key, $warmCallback);
    }

    /**
     * Warm cache by calling the callback
     */
    public function warm(string $key, callable $warmCallback): void
    {
        try {
            $warmCallback();
            error_log("Cache warmed for key: {$key}");
        } catch (\Exception $e) {
            error_log("Cache warming failed for key {$key}: " . $e->getMessage());
        }
    }

    /**
     * Clear multiple cache keys
     */
    public function deleteMultiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * Clear multiple cache keys and warm them
     */
    public function clearAndWarmMultiple(array $keyCallbacks): void
    {
        foreach ($keyCallbacks as $key => $callback) {
            $this->clearAndWarm($key, $callback);
        }
    }

    /**
     * Get cache information for debugging
     */
    public function getInfo(?string $key = null): array
    {
        $info = [
            'cache_prefix' => $this->prefix,
            'cache_type' => get_class($this->cache),
        ];

        if ($key) {
            $fullKey = $this->prefix . '-' . $key;
            $info['cache_key'] = $fullKey;
            $info['cache_exists'] = $this->cache->hasItem($fullKey);
        }

        return $info;
    }

    /**
     * Get the underlying cache adapter
     */
    public function getAdapter()
    {
        return $this->cache;
    }

    /**
     * Get the cache prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
