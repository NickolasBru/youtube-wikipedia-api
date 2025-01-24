<?php

namespace App\Services\Cache;

use App\Interfaces\CacheInterface;
use Illuminate\Support\Facades\Cache;
use Closure;

class LaravelImplementation implements CacheInterface
{
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    public function put(string $key, $value, int $seconds): void
    {
        Cache::put($key, $value, $seconds);
    }

    public function remember(string $key, int $seconds, Closure $callback)
    {
        return Cache::remember($key, $seconds, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }
}
