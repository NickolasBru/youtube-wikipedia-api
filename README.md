# Bravoure BE Challenge

This Laravel-based application fetches and merges data from **YouTube** (most popular videos) and **Wikipedia** (leading paragraphs) for several European countries. It demonstrates a clean architecture with interfaces, aggregators, caching, and rate limiting.

---

## Installation
### Using Docker

If you prefer a Docker-based setup:

1. **Build & start containers**:
   ```bash
   docker compose up -d --build
   ```
2. **Copy `.env.example` to `.env`** inside the container:
   ```bash
   cp .env.example .env
   ```

3. **Install composer** inside the container:
   ```bash
    composer install
   ```
4. **Generate app key** inside the container:
   ```bash
    php artisan key:generate
   ```

5. **Update youtube api key** on .env update the following field with your key:
   ```bash
     YOUTUBE_API_KEY
   ```

The application should now be running on the port you configured in `docker-compose.yml` (commonly `http://localhost:8080`).

---

## Configuration

### Cache Setup with SQLite

1. **Create the SQLite Database File**  
   Run the following command inside the container to create an empty SQLite file:
   ```bash
   touch database/cache.sqlite
   ```

2. **Update `.env` Configuration**  
   Ensure your `.env` file has the following entries:
   ```env
   CACHE_DRIVER=database
   DB_CONNECTION=sqlite
   DB_DATABASE=database/cache.sqlite
   ```

3. **Create the Cache Table**  
   Generate the cache table migration and run it:
   ```bash
   php artisan cache:table
   php artisan migrate
   ```

4. **Verify the Setup**  
   Use Artisan Tinker to test caching:
   ```bash
   php artisan tinker
   Cache::has('test');
   ```

---

## Running the Application
- **Using Docker**:
  Navigate to [http://localhost:8080](http://localhost:8080) (or whatever port is mapped in your `docker-compose.yml`).

---

## API Endpoints

### `POST /api/v1/countries`

**Supported Formats**:
- Send parameters as query strings:
  ```
  http://localhost:8080/api/v1/countries?country=nl&page=1&offset=3&force_refresh=1
  ```
- Or send parameters in the request body (JSON):
  ```json
  {
      "country": "nl",
      "page": 1,
      "offset": 3,
      "force_refresh": true
  }
  ```

**Parameters**:
- **`country`** (string, optional): One of `gb`, `nl`, `de`, `fr`, `es`, `it`, `gr`. Defaults to all countries if omitted. (Using GB for the UK since youtube uses the ISO 3166-1 alpha-2, and on that case the UK is refered as gb)
- **`page`** (integer, optional): Defaults to `1`.
- **`offset`** (integer, optional): Defaults to `5`.
- **`force_refresh`** (boolean, optional): If `true`, clears the cache for the specified countries before fetching.

**Sample Request**:
```json
POST /api/v1/countries HTTP/1.1
Content-Type: application/json

{
  "country": "nl",
  "page": 2,
  "offset": 5,
  "force_refresh": true
}
```

**Sample JSON Response**:
```json
{
  "page": 2,
  "limit": 5,
  "total": 7,
  "data": [
    {
      "country_code": "nl",
      "wikipedia": "The Netherlands is a country in ...",
      "youtube": [
        {
          "title": "Some popular video",
          "description": "A sample description",
          "thumbnails": {
            "normal": "https://youtube.com/...",
            "high": "https://youtube.com/..."
          }
        }
      ]
    }
  ]
}
```

---

## Testing

Run the test suite with:

```bash
php artisan test
```
or
```bash
vendor/bin/phpunit
```

### Unit Tests
- **`tests/Unit/CountryDataAggregatorTest.php`**: Uses Mockery to mock the video/encyclopedia/cache providers. Verifies data is fetched, cached, and merged correctly.

---

## Additional Tips

1. **Force Refresh**
    - Add `?force_refresh=1` in query strings or `"force_refresh": true` in the request body to clear the cache and fetch new data from YouTube/Wikipedia.
2. **Multiple Countries**
    - If `country` is omitted, the aggregator returns a combined array for `[gb, nl, de, fr, es, it, gr]`.
3. **Swapping Data Sources**
    - Easily replace **YouTube** or **Wikipedia** by implementing the respective interfaces. Update your service provider binding accordingly.
4. **Rate Limits**
    - You can configure how many YouTube calls per minute are allowed by adjusting `RateLimiter` usage in the `YouTubeVideoProvider`.
---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE). You’re free to modify or distribute as needed.
