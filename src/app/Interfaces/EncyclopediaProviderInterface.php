<?php

namespace App\Interfaces;

interface EncyclopediaProviderInterface
{
    /**
     * Get the lead/intro paragraph for a given country code.
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getCountryLeadParagraph(string $countryCode): ?string;
}
