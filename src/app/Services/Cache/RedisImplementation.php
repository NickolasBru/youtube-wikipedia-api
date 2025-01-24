<?php

namespace App\Services\Cache;

use App\Interfaces\CacheInterface;
use Illuminate\Support\Facades\Cache;
use Closure;

class RedisImplementation implements CacheInterface
{
    public function get(string $key, $default = null)
    {
        // TODO: Implement get() method for redis cache.
    }

    public function put(string $key, $value, int $seconds): void
    {
        // TODO: Implement put() method for redis cache.
    }

    public function remember(string $key, int $seconds, Closure $callback)
    {
        // TODO: Implement remember() method for redis cache.
    }

    public function forget(string $key): void
    {
        // TODO: Implement forget() method for redis cache.
    }
}
