<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Log;
use App\Interfaces\VideoProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class YouTubeProvider implements VideoProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
        $this->baseUrl = 'https://www.googleapis.com/youtube/v3';
    }

    public function getPopularVideos(string $countryCode, int $limit = 5): array
    {
        // Define the key
        $limiterKey = 'youtube_api';

        //set attempt values
        $maxAttempts = 21;
        $decaySeconds = 60;

        // 3) Check if we've exceeded the limit
        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {

            //Log and return
            Log::warning("YouTube API rate limit reached.");
            throw new \RuntimeException('YouTube API rate limit exceeded.');
        }

        // increments the usage count.
        RateLimiter::hit($limiterKey, $decaySeconds);

        //Proceed with the actual call if we haven't exceeded the limit
        $response = Http::get("{$this->baseUrl}/videos", [
            'part'       => 'snippet',
            'chart'      => 'mostPopular',
            'regionCode' => $countryCode,
            'maxResults' => $limit,
            'key'        => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::error('YouTube API call failed.', [
                'country' => $countryCode,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return [];
        }

        $json = $response->json();
        return $json['items'] ?? [];
    }
}
