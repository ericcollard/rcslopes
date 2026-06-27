<?php
namespace helpers;

class CacheHelper {
    private $cacheFile;

    public function __construct($cacheFile = 'cache/last_api_call.txt') {
        $this->cacheFile = $cacheFile;

        // Create cache directory if it doesn't exist
        $dir = dirname($this->cacheFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public function shouldRefreshCache($duration = 60) {
        if (!file_exists($this->cacheFile)) {
            return true;
        }

        $lastCallTime = file_get_contents($this->cacheFile);
        $currentTime = time();

        return ($currentTime - $lastCallTime) > $duration;
    }

    public function updateCacheTime() {
        file_put_contents($this->cacheFile, time());
    }

    public function getLastCacheTime() {
        if (file_exists($this->cacheFile)) {
            return file_get_contents($this->cacheFile);
        }
        return null;
    }
}
?>