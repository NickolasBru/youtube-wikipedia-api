<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Services\Aggregators\CountryDataAggregator;
use App\Interfaces\VideoProviderInterface;
use App\Interfaces\EncyclopediaProviderInterface;
use App\Interfaces\CacheInterface;

class CountryDataAggregatorTest extends TestCase
{
    /**
     * Test aggregator merges data from dependencies
     * and returns the expected structure (cache miss scenario).
     */
    public function testGetCountryDataWithoutCache(): void
    {
        // 1. Create Mocks
        $videoProviderMock = Mockery::mock(VideoProviderInterface::class);
        $encyclopediaMock  = Mockery::mock(EncyclopediaProviderInterface::class);
        $cacheMock         = Mockery::mock(CacheInterface::class);

        // 2. Define some fake return data
        //    (what we expect from the video/encyclopedia providers)
        $fakeVideoData = [
            [
                'snippet' => [
                    'title'       => 'Test Video',
                    'description' => 'A test description',
                    'thumbnails'  => [
                        'default' => ['url' => 'http://example.com/default.jpg'],
                        'high'    => ['url' => 'http://example.com/high.jpg'],
                    ],
                ],
            ],
        ];
        $fakeParagraph = 'Wikipedia excerpt for the gb';

        // 3. Mock the behavior
        // a) Video provider is called exactly once for 'gb'
        $videoProviderMock
            ->shouldReceive('getPopularVideos')
            ->once()
            ->with('gb')
            ->andReturn($fakeVideoData);

        // b) Encyclopedia provider is called exactly once for 'gb'
        $encyclopediaMock
            ->shouldReceive('getCountryLeadParagraph')
            ->once()
            ->with('gb')
            ->andReturn($fakeParagraph);

        // c) Because forceRefresh is false, aggregator won't call ->forget()
        //    but it will call ->remember(). We simulate a cache miss
        //    by having ->remember() execute the callback and return its result.
        $cacheMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $seconds, $callback) {
                // We can add an assertion on the cache key if we want
                $this->assertSame('country_data_gb', $key, 'Cache key mismatch');
                $this->assertSame(1800, $seconds, 'Cache TTL mismatch');
                return true; // to let Mockery know the arguments matched
            })
            ->andReturnUsing(function ($key, $seconds, $callback) {
                // Simulate "cache miss" -> run the callback
                return $callback();
            });

        // 4. Instantiate aggregator with the mocks
        $aggregator = new CountryDataAggregator($videoProviderMock, $encyclopediaMock, $cacheMock);

        // 5. Call aggregator
        //    countries=['gb'], page=1, limit=5, forceRefresh=false
        $result = $aggregator->getCountryData(['gb'], 1, 5, false);

        // 6. Assert the structure
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('data', $result);

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(5, $result['limit']);
        $this->assertEquals(1, $result['total']);  // 1 country

        // The 'data' should have 1 item
        $this->assertCount(1, $result['data']);
        $countryEntry = $result['data'][0];
        $this->assertEquals('gb', $countryEntry['country_code']);
        $this->assertEquals($fakeParagraph, $countryEntry['wikipedia']);

        // Videos
        $this->assertArrayHasKey('youtube', $countryEntry);
        $this->assertCount(1, $countryEntry['youtube']);
        $this->assertEquals('Test Video', $countryEntry['youtube'][0]['title']);
    }

    /**
     * Test force refresh logic. Aggregator calls forget() then re-fetches.
     */
    public function testForceRefresh(): void
    {
        $videoProviderMock = Mockery::mock(VideoProviderInterface::class);
        $encyclopediaMock  = Mockery::mock(EncyclopediaProviderInterface::class);
        $cacheMock         = Mockery::mock(CacheInterface::class);

        // If forceRefresh = true, aggregator calls ->forget(...)
        $cacheMock
            ->shouldReceive('forget')
            ->once()
            ->with('country_data_gb');

        // We'll also expect a ->remember(...) call
        $cacheMock
            ->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $seconds, $callback) {
                return $callback(); // Simulate a cache miss
            });

        // For simplicity, let's say the providers return empty
        $videoProviderMock
            ->shouldReceive('getPopularVideos')
            ->once()
            ->with('gb')
            ->andReturn([]);
        $encyclopediaMock
            ->shouldReceive('getCountryLeadParagraph')
            ->once()
            ->with('gb')
            ->andReturn(null);

        $aggregator = new CountryDataAggregator($videoProviderMock, $encyclopediaMock, $cacheMock);

        // Pass forceRefresh=true
        $result = $aggregator->getCountryData(['gb'], 1, 5, true);

        // We still expect the final structure
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('data', $result);

        // data => [ [ 'country_code' => 'gb', 'wikipedia' => null, 'youtube' => [] ] ]
        $this->assertEquals(1, $result['total']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('gb', $result['data'][0]['country_code']);
        $this->assertIsArray($result['data'][0]['youtube']);
        $this->assertEmpty($result['data'][0]['youtube']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
