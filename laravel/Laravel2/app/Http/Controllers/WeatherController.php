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

        if (! config('services.openweather.key')) {
            return response()->json(['message' => 'Weather is not configured.'], 503);
        }

        $cacheKey = sprintf('weather:%.3f:%.3f', $data['lat'], $data['lon']);

        try {
            $weather = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($data) {
                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $data['lat'],
                    'lon' => $data['lon'],
                    'units' => 'metric',
                    'appid' => config('services.openweather.key'),
                ]);

                if ($response->failed()) {
                    throw new \RuntimeException('Weather provider unavailable');
                }

                return $response->json();
            });
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Weather is currently unavailable.'], 502);
        }

        return response()->json([
            'place' => $weather['name'] ?? 'Selected location',
            'temperature' => $weather['main']['temp'] ?? null,
            'description' => $weather['weather'][0]['description'] ?? null,
            'humidity' => $weather['main']['humidity'] ?? null,
        ]);
    }
}
