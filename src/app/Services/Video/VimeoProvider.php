<?php

namespace App\Services\Video;

use App\Interfaces\VideoProviderInterface;
use Illuminate\Support\Facades\Http;

class VimeoProvider implements VideoProviderInterface
{
    public function getPopularVideos(string $countryCode, int $limit = 5): array
    {
        // TODO: Implement getPopularVideos() method for the vimeo provider.

        return [];
    }
}
