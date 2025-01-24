<?php

namespace App\Services\Aggregators;

use App\Interfaces\CountryDataAggregatorInterface;
use App\Interfaces\VideoProviderInterface;
use App\Interfaces\EncyclopediaProviderInterface;
use App\Interfaces\CacheInterface;

class CountryDataAggregator implements CountryDataAggregatorInterface
{
    private VideoProviderInterface $videoProvider;
    private EncyclopediaProviderInterface $encyclopediaProvider;
    private CacheInterface $cache;

    public function __construct(
        VideoProviderInterface $videoProvider,
        EncyclopediaProviderInterface $encyclopediaProvider,
        CacheInterface $cache
    ) {
        $this->videoProvider = $videoProvider;
        $this->encyclopediaProvider = $encyclopediaProvider;
        $this->cache = $cache;
    }

    public function getCountryData(
        array $countries,
        int $page = 1,
        int $limit = 5,
        bool $forceRefresh = false
    ): array {
        //Build the cache key
        $cacheKey = $this->generateCacheKey($countries);

        //If is supposed to refresh, clear the cache
        if ($forceRefresh) {
            $this->cache->forget($cacheKey);
        }

        //Retrieve the data
        $mergedResults = $this->cache->remember(
            $cacheKey,
            1800, // 30 minutes in seconds
            function () use ($countries) {
                return $this->fetchCountriesData($countries);
            }
        );

        //Paginate
        $paginated = $this->paginate($mergedResults, $page, $limit);

        return [
            'page'  => $page,
            'limit' => $limit,
            'total' => count($mergedResults),
            'data'  => $paginated,
        ];
    }

    /**
     * Build the data for all requested countries.
     */
    private function fetchCountriesData(array $countries): array
    {
        $results = [];
        foreach ($countries as $countryCode) {
            $results[] = $this->fetchSingleCountryData($countryCode);
        }
        return $results;
    }

    /**
     * Fetch data for a single country from video & encyclopedia providers, and map it.
     */
    private function fetchSingleCountryData(string $countryCode): array
    {
        // Fetch videos
        $videos = $this->videoProvider->getPopularVideos($countryCode);

        // Map out the relevant info (description, thumbs)
        $mappedVideos = array_map(function($item) {
            return $this->mapVideoItem($item);
        }, $videos);

        // Fetch encyclopedia data
        $paragraph = $this->encyclopediaProvider->getCountryLeadParagraph($countryCode);

        return [
            'country_code' => $countryCode,
            'wikipedia'    => $paragraph,
            'youtube'      => $mappedVideos,
        ];
    }

    /**
     * Map a single video item (e.g. from YouTube) to a standardized structure.
     */
    private function mapVideoItem(array $videoItem): array
    {
        $snippet = $videoItem['snippet'] ?? [];

        return [
            'title'       => $snippet['title'] ?? null,
            'description' => $snippet['description'] ?? null,
            'thumbnails'  => [
                'normal' => $snippet['thumbnails']['default']['url'] ?? null,
                'high'   => $snippet['thumbnails']['high']['url'] ?? null,
            ],
        ];
    }

    /**
     * Perform in-memory pagination.
     */
    private function paginate(array $data, int $page, int $limit): array
    {
        $startIndex = ($page - 1) * $limit;
        return array_slice($data, $startIndex, $limit);
    }

    /**
     * Build a unique cache key for the specified countries.
     */
    private function generateCacheKey(array $countries): string
    {
        return 'country_data_' . implode('_', $countries);
    }
}
