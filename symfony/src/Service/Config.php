<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @author Al Zee <z@alz.ee>
 * @version
 * @todo
 */

namespace App\Service;

use App\Repository\ConfRepository;

class Config
{
    public function __construct(
        private ConfRepository $confRepo,
        private CacheService $cacheService
    ) {
    }

    public function getAll(): array
    {
        return $this->cacheService->get('conf', function() {
            $confs = $this->confRepo->findAll();
            $configs = [];
            foreach ($confs as $conf) {
                $configs[$conf->getKey()] = $conf->getValue();
            }

            return $configs;
        });
    }

    public function get(string $key): string
    {
        return $this->cacheService->get('conf-' . $key, function() use ($key){
            $conf = $this->confRepo->findOneBy(['key' => $key]);

            return $conf->getValue() ?? '';
        });
    }

    public function clearCache(): void
    {
        $this->cacheService->clearAndWarm('conf', function() {
            $this->warmCache();
        });
    }

    public function clearCacheForKey(string $key): void
    {
        $this->cacheService->clearAndWarm('conf-' . $key, function() use ($key) {
            $this->warmCacheForKey($key);
        });
    }

    public function getCacheInfo(): array
    {
        return $this->cacheService->getInfo('conf');
    }

    /**
     * Warm the cache by pre-loading all config data
     */
    public function warmCache(): void
    {
        try {
            // Pre-load all configs
            $this->getAll();
            
            // Pre-load individual configs
            $confs = $this->confRepo->findAll();
            foreach ($confs as $conf) {
                $this->get($conf->getKey());
            }
            
            error_log('Config cache warmed successfully');
        } catch (\Exception $e) {
            error_log('Config cache warming failed: ' . $e->getMessage());
        }
    }

    /**
     * Warm the cache for a specific key
     */
    public function warmCacheForKey(string $key): void
    {
        try {
            $this->get($key);
            error_log("Config cache warmed for key: {$key}");
        } catch (\Exception $e) {
            error_log("Config cache warming failed for key {$key}: " . $e->getMessage());
        }
    }
}
