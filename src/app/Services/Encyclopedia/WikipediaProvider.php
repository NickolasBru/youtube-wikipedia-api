<?php

namespace App\Services\Encyclopedia;

use App\Interfaces\EncyclopediaProviderInterface;
use Illuminate\Support\Facades\Http;

class WikipediaProvider implements EncyclopediaProviderInterface
{
    private string $baseUrl;

    public function __construct()
    {
        // Using Wikipedia REST API, summary endpoint
        $this->baseUrl = 'https://en.wikipedia.org/api/rest_v1/page/summary/';
    }

    public function getCountryLeadParagraph(string $countryCode): ?string
    {
        // Map the code to an article title
        $articleTitle = $this->mapToArticleTitle($countryCode);

        $response = Http::get("{$this->baseUrl}{$articleTitle}");
        if ($response->successful()) {
            $data = $response->json();
            return $data['extract'] ?? null;
        }
        return null;
    }

    private function mapToArticleTitle(string $code): string
    {
        return match (strtolower($code)) {
            'gb' => 'United_Kingdom',
            'nl' => 'Netherlands',
            'de' => 'Germany',
            'fr' => 'France',
            'es' => 'Spain',
            'it' => 'Italy',
            'gr' => 'Greece',
            default => 'United_Kingdom',
        };
    }
}
