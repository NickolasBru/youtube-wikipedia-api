<?php

namespace App\Services\Encyclopedia;

use App\Interfaces\EncyclopediaProviderInterface;
use Illuminate\Support\Facades\Http;

class BritannicaProvider implements EncyclopediaProviderInterface
{

    public function getCountryLeadParagraph(string $countryCode): ?string
    {
        // TODO: Implement getCountryLeadParagraph() method for the Britannica provider
        return true;
    }
}
