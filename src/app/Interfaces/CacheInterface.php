<?php

namespace App\Interfaces;

use Closure;

interface CacheInterface
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string  $key
     * @param mixed   $value
     * @param int     $seconds
     * @return void
     */
    public function put(string $key, $value, int $seconds): void;

    /**
     * Retrieve an item from the cache, or store the default value.
     *
     * @param string  $key
     * @param int     $seconds
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $seconds, Closure $callback);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     */
    public function forget(string $key): void;
}
