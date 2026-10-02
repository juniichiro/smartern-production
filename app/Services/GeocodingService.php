<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $baseUrl;
    protected string $userAgent;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.geocoding.base_url', 'https://nominatim.openstreetmap.org'), '/');
        $this->userAgent = config('services.geocoding.user_agent', 'SmarTern-Admin/1.0 (smartern.edu; contact: admin@smartern.com)');
        $this->timeout = (int) config('services.geocoding.timeout', 4);
    }

    /**
     * Reverse geocode latitude and longitude to a human-readable address.
     * Caches successful lookups for 30 days and failures for 5 minutes.
     */
    public function reverse(float $latitude, float $longitude): ?string
    {
        $roundedLat = number_format($latitude, 5, '.', '');
        $roundedLng = number_format($longitude, 5, '.', '');
        $cacheKey = "geocode_rev_{$roundedLat}_{$roundedLng}";

        // If previously cached as failed (empty string sentinel), return null
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return $cached === '' ? null : $cached;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->get("{$this->baseUrl}/reverse", [
                'format' => 'json',
                'lat' => $roundedLat,
                'lon' => $roundedLng,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $displayName = $data['display_name'] ?? null;

                if (! empty($displayName)) {
                    // Cache successful lookup for 30 days
                    Cache::put($cacheKey, $displayName, now()->addDays(30));
                    return $displayName;
                }
            }

            // Cache failure sentinel for 5 minutes to avoid hammering
            Cache::put($cacheKey, '', now()->addMinutes(5));
            return null;
        } catch (\Throwable $e) {
            Log::warning("Geocoding reverse lookup failed for {$roundedLat}, {$roundedLng}: " . $e->getMessage());
            Cache::put($cacheKey, '', now()->addMinutes(5));
            return null;
        }
    }

    /**
     * Search for places matching a text query.
     * Caches results for 7 days.
     */
    public function search(string $query): array
    {
        $cleaned = trim($query);
        if (mb_strlen($cleaned) < 3) {
            return [];
        }

        $cacheKey = 'geocode_search_' . md5(mb_strtolower($cleaned));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($cleaned) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/search", [
                    'format' => 'json',
                    'q' => $cleaned,
                    'limit' => 5,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $items = $response->json();
                    if (is_array($items)) {
                        return array_map(function ($item) {
                            return [
                                'display_name' => $item['display_name'] ?? '',
                                'latitude' => (float) ($item['lat'] ?? 0),
                                'longitude' => (float) ($item['lon'] ?? 0),
                            ];
                        }, $items);
                    }
                }

                return [];
            } catch (\Throwable $e) {
                Log::warning("Geocoding search failed for query '{$cleaned}': " . $e->getMessage());
                return [];
            }
        });
    }
}
