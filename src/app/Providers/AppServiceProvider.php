<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts (interfaces)
use App\Interfaces\VideoProviderInterface;
use App\Interfaces\EncyclopediaProviderInterface;
use App\Interfaces\CacheInterface;
use App\Interfaces\CountryDataAggregatorInterface;

// Concrete implementations
use App\Services\Video\YouTubeProvider;
use App\Services\Encyclopedia\WikipediaProvider;
use App\Services\Cache\LaravelImplementation;
use App\Services\Aggregators\CountryDataAggregator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the providers and cache
        $this->app->bind(VideoProviderInterface::class, function ($app) {
            return new YouTubeProvider();
        });
        $this->app->bind(EncyclopediaProviderInterface::class, function ($app) {
            return new WikipediaProvider();
        });
        $this->app->bind(CacheInterface::class, function ($app) {
            return new LaravelImplementation();
        });

        //bind the aggregator
        $this->app->bind(CountryDataAggregatorInterface::class, function ($app) {
            return new CountryDataAggregator(
                $app->make(VideoProviderInterface::class),
                $app->make(EncyclopediaProviderInterface::class),
                $app->make(CacheInterface::class),
            );
        });
    }

    public function boot()
    {
        //
    }
}
