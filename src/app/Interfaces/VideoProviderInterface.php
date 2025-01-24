<?php

namespace App\Interfaces;

interface VideoProviderInterface
{
    /**
     * Fetch the most popular videos for a given country code.
     *
     * @param string $countryCode
     * @param int    $limit
     * @return array
     */
    public function getPopularVideos(string $countryCode, int $limit = 5): array;
}
