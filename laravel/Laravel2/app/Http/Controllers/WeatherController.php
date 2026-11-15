<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $cacheKey = sprintf('weather:%.3f:%.3f', $data['lat'], $data['lon']);

        try {
            $weather = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($data) {
                $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                    'current' => 'temperature_2m,relative_humidity_2m,weather_code',
                    'temperature_unit' => 'celsius',
                    'timezone' => 'auto',
                ]);

                if ($response->failed()) {
                    throw new \RuntimeException('Weather provider unavailable');
                }

                return $response->json();
            });
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 502);
        }

        $current = $weather['current'] ?? [];
        $weatherCode = (int) ($current['weather_code'] ?? -1);

        return response()->json([
            'place' => 'Selected location',
            'temperature' => $current['temperature_2m'] ?? null,
            'description' => $this->describeWeatherCode($weatherCode),
            'icon' => $this->weatherIconForCode($weatherCode),
            'humidity' => $current['relative_humidity_2m'] ?? null,
        ]);
    }

    private function weatherIconForCode(int $code): string
    {
        return match (true) {
            in_array($code, [0, 1], true) => 'sun',
            $code === 2 => 'partly-cloudy',
            in_array($code, [3, 45, 48], true) => 'cloud',
            in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82], true) => 'rain',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'snow',
            in_array($code, [95, 96, 99], true) => 'storm',
            default => 'cloud',
        };
    }

    private function describeWeatherCode(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45, 48 => 'Foggy',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            56, 57 => 'Freezing drizzle',
            61 => 'Light rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            66, 67 => 'Freezing rain',
            71 => 'Light snow',
            73 => 'Moderate snow',
            75 => 'Heavy snow',
            77 => 'Snow grains',
            80 => 'Light rain showers',
            81 => 'Moderate rain showers',
            82 => 'Heavy rain showers',
            85 => 'Light snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96, 99 => 'Thunderstorm with hail',
            default => 'Current conditions',
        };
    }
}
