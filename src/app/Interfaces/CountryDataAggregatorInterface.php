<?php

namespace App\Interfaces;

interface CountryDataAggregatorInterface
{
    /**
     * Fetch merged data for multiple countries, with optional pagination and cache refresh logic.
     *
     * @param array $countries
     * @param int   $page
     * @param int   $limit
     * @param bool  $forceRefresh
     * @return array
     */
    public function getCountryData(
        array $countries,
        int $page = 1,
        int $limit = 5,
        bool $forceRefresh = false
    ): array;
}
