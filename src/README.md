# Youtube & Wikipedia API BE Challenge

This Laravel-based application fetches and merges data from **YouTube** (most popular videos) and **Wikipedia** (leading paragraphs) for several European countries. It demonstrates a clean architecture with interfaces, aggregators, caching, and rate limiting.

---

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/NickolasBru/youtube-wikipedia-resume-api
   cd youtube-wikipedia-resume-api
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Copy `.env.example` to `.env`**:
   ```bash
   cp .env.example .env
   ```
   Then fill in your environment variables (e.g., `APP_KEY`, `YOUTUBE_API_KEY`, `CACHE_DRIVER`, etc.).

4. **Generate the app key**:
   ```bash
   php artisan key:generate
   ```

### Using Docker (Optional)

If you prefer a Docker-based setup:

1. **Build & start containers**:
   ```bash
   docker compose up -d --build
   ```

2. **Install dependencies** inside the container:
   ```bash
   docker compose exec app composer install
   ```

3. **Generate app key**:
   ```bash
   docker compose exec app php artisan key:generate
   ```

The application should now be running on the port you configured in `docker-compose.yml` (commonly `http://127.0.0.1:8080`).

---

## Configuration

Open your `.env` file and verify or set the following variables:

```env
YOUTUBE_API_KEY=YOUR_ACTUAL_YOUTUBE_KEY
```

- **YOUTUBE_API_KEY**: Required for calling the YouTube Data API (videos.list).
- **CACHE_DRIVER**: By default, you can use `file`; for better performance in production, consider `redis`.
- Adjust DB/Redis credentials as needed if you’re using them.

---

## Running the Application

- **Local (without Docker)**:
  ```bash
  php artisan serve
  ```
  Visit [http://127.0.0.1:8000](http://127.0.0.1:8000).

- **Using Docker**:
  Navigate to [http://127.0.0.1:8080](http://127.0.0.1:8080) (or whatever port is mapped in your `docker-compose.yml`).

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
- **`country`** (string, optional): One of `gb`, `nl`, `de`, `fr`, `es`, `it`, `gr`. Defaults to all countries if omitted.
- **`page`** (integer, optional): Defaults to `1`.
- **`offset`** (integer, optional): Defaults to `5`.
- **`force_refresh`** (boolean, optional): If `true`, clears the cache for the specified countries before fetching.

**Sample Request**:
```json
POST /api/v1/countries HTTP/1.1
Content-Type: application/json

{
  "country": "nl",
  "page": 1,
  "offset": 5,
  "force_refresh": true
}
```

**Sample JSON Response**:
```json
{
  "page": 1,
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
    - Add `?force_refresh=1` to clear the cache and fetch new data from YouTube/Wikipedia.
2. **Multiple Countries**
    - If `country` is omitted, the aggregator returns a combined array for `[gb, nl, de, fr, es, it, gr]`.
3. **Swapping Data Sources**
    - Easily replace **YouTube** or **Wikipedia** by implementing the respective interfaces. Update your service provider binding accordingly.
4. **Rate Limits**
    - You can configure how many YouTube calls per minute are allowed by adjusting `RateLimiter` usage in the `YouTubeVideoProvider`.
---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE). You’re free to modify or distribute as needed.
