<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class NrkVehicleStatusService
{
    private const CACHE_KEY = 'nrk:vehicle-statuses:v1';
    private const PROXY_URL = 'https://intern.rkhl.at/api/vehicle-statuses';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function getStatuses(?string $authorization = null): array
    {
        if (app()->environment('local')) {
            return $this->fetchFromProxy($authorization);
        }

        $cacheHit = Cache::has(self::CACHE_KEY);
        $cacheSeconds = max(1, (int) config('custom.NRK_VEHICLE_STATUS_CACHE_SECONDS', 300));

        $payload = Cache::remember(
            self::CACHE_KEY,
            now()->addSeconds($cacheSeconds),
            function () use ($cacheSeconds) {
                return $this->fetchStatuses($cacheSeconds);
            }
        );

        $payload['meta']['cache_hit'] = $cacheHit;

        return $payload;
    }

    private function fetchFromProxy(?string $authorization): array
    {
        try {
            $request = Http::acceptJson()->timeout(15);

            if ($authorization !== null && $authorization !== '') {
                $request = $request->withHeaders([
                    'Authorization' => $authorization,
                ]);
            }

            $response = $request->get(self::PROXY_URL);
        } catch (Throwable $exception) {
            throw new RuntimeException('NRK vehicle status proxy request failed.', 0, $exception);
        }

        $payload = $response->json();

        if (!$response->successful() || !is_array($payload) || !isset($payload['data'])) {
            throw new RuntimeException(
                'NRK vehicle status proxy returned an unusable response (HTTP '.$response->status().').'
            );
        }

        return $payload;
    }

    private function fetchStatuses(int $cacheSeconds): array
    {
        $server = (string) config('custom.NRKAPISERVER');
        $apiKey = (string) config('custom.NRKAPIKEY');
        $vehicles = Vehicle::query()
            ->where('active', true)
            ->orderBy('funkkennung')
            ->pluck('funkkennung')
            ->all();

        if ($server === '' || $apiKey === '' || empty($vehicles)) {
            throw new RuntimeException('NRK vehicle status is not configured.');
        }

        try {
            $responses = Http::pool(function (Pool $pool) use ($server, $apiKey, $vehicles) {
                foreach ($vehicles as $callsign) {
                    $pool->as($callsign)
                        ->withHeaders([
                            'NRK-AUTH' => $apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->acceptJson()
                        ->timeout(15)
                        ->post($server, [
                            'req' => 'KFZStatus',
                            'funkkennung' => $callsign,
                        ]);
                }
            });
        } catch (Throwable $exception) {
            throw new RuntimeException('NRK vehicle status request failed.', 0, $exception);
        }

        $statuses = [];
        $successfulResponses = 0;

        foreach ($vehicles as $callsign) {
            $response = $responses[$callsign] ?? null;
            $status = $this->normalizeResponse($callsign, $response);

            if ($status['available']) {
                $successfulResponses++;
            }

            $statuses[] = $status;
        }

        if ($successfulResponses === 0) {
            throw new RuntimeException('NRK returned no usable vehicle statuses.');
        }

        $fetchedAt = now();

        return [
            'data' => $statuses,
            'meta' => [
                'fetched_at' => $fetchedAt->toIso8601String(),
                'expires_at' => $fetchedAt->copy()->addSeconds($cacheSeconds)->toIso8601String(),
                'cache_seconds' => $cacheSeconds,
            ],
        ];
    }

    private function normalizeResponse(string $callsign, $response): array
    {
        $unavailable = [
            'funkkennung' => $callsign,
            'available' => false,
            'type' => null,
            'callsign' => null,
            'status' => null,
            'status_time' => null,
            'fahrer' => null,
            'san1' => null,
            'san2' => null,
            'arzt' => null,
        ];

        if (!$response instanceof Response || !$response->successful()) {
            return $unavailable;
        }

        $body = $response->json();

        if (
            !is_array($body)
            || ($body['status'] ?? null) !== 'OK'
            || !isset($body['data'])
            || !is_array($body['data'])
        ) {
            return $unavailable;
        }

        $data = $body['data'];

        return [
            'funkkennung' => $data['funkkennung'] ?? $callsign,
            'available' => true,
            'type' => $data['ressource_type'] ?? null,
            'callsign' => $data['ressource_callsign'] ?? null,
            'status' => $data['ressource_status'] ?? null,
            'status_time' => $data['status_time'] ?? null,
            'fahrer' => $data['fahrer'] ?? null,
            'san1' => $data['san1'] ?? null,
            'san2' => $data['san2'] ?? null,
            'arzt' => $data['arzt'] ?? null,
        ];
    }
}
