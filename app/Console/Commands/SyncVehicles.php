<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\NrkVehicleStatusService;
use App\Support\VehicleCallsign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncVehicles extends Command
{
    protected $signature = 'command:syncVehicles';

    protected $description = 'Synchronisiert bekannte Fahrzeuge aus den Incode-Dienstplänen des letzten Monats';

    public function handle()
    {
        $server = (string) config('custom.NRKAPISERVER');
        $apiKey = (string) config('custom.NRKAPIKEY');
        $planIds = config('custom.NRK_PLAN_IDS', []);

        if ($server === '' || $apiKey === '' || empty($planIds)) {
            $this->error('Die NRK API oder die Incode-Pläne sind nicht konfiguriert.');

            return 1;
        }

        $from = now()->subMonth()->startOfDay();
        $until = now()->endOfDay();
        $callsigns = [];

        foreach ($planIds as $planId) {
            $chunkStart = $from->copy();

            while ($chunkStart->lte($until)) {
                $chunkEnd = $chunkStart->copy()->addDays(13)->min($until);
                try {
                    $response = Http::withHeaders([
                        'NRK-AUTH' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                        ->acceptJson()
                        ->timeout(30)
                        ->post($server, [
                            'req' => 'GET_INCODE_PLAN',
                            'von' => $chunkStart->toDateString(),
                            'bis' => $chunkEnd->toDateString(),
                            'OIDOrgEinheit' => $planId,
                        ]);
                } catch (Throwable $exception) {
                    report($exception);
                    $this->error("NRK-Abfrage für Plan {$planId} ist fehlgeschlagen.");

                    return 1;
                }

                if (!$response->successful()) {
                    $this->error("NRK-Abfrage für Plan {$planId} ist fehlgeschlagen.");

                    return 1;
                }

                $body = $response->json();

                if (
                    !is_array($body)
                    || ($body['status'] ?? null) !== 'OK'
                    || !isset($body['data']['plan'])
                    || !is_array($body['data']['plan'])
                ) {
                    $this->error("Ungültige NRK-Antwort für Plan {$planId}.");

                    return 1;
                }

                $this->collectCallsigns($body['data']['plan'], $callsigns);
                $chunkStart = $chunkEnd->copy()->addDay();
            }
        }

        if (empty($callsigns)) {
            $this->error('Es wurden keine Fahrzeuge gefunden. Der bestehende Fahrzeugkatalog bleibt unverändert.');

            return 1;
        }

        $seenAt = now();
        $previousActive = Vehicle::query()
            ->where('active', true)
            ->orderBy('funkkennung')
            ->pluck('funkkennung')
            ->all();
        $discovered = array_keys($callsigns);
        sort($discovered);

        DB::transaction(function () use ($callsigns, $seenAt) {
            Vehicle::query()
                ->whereNotIn('funkkennung', array_keys($callsigns))
                ->update(['active' => false]);

            $rows = [];

            foreach (array_keys($callsigns) as $callsign) {
                $rows[] = [
                    'funkkennung' => $callsign,
                    'active' => true,
                    'last_seen_at' => $seenAt,
                    'created_at' => $seenAt,
                    'updated_at' => $seenAt,
                ];
            }

            Vehicle::upsert(
                $rows,
                ['funkkennung'],
                ['active', 'last_seen_at', 'updated_at']
            );
        });

        if ($previousActive !== $discovered) {
            NrkVehicleStatusService::clearCache();
        }

        $this->info(count($callsigns).' Fahrzeuge wurden synchronisiert.');

        return 0;
    }

    private function collectCallsigns(array $plans, array &$callsigns): void
    {
        foreach ($plans as $shift) {
            foreach ($shift['ressources'] ?? [] as $resource) {
                if (($resource['typid'] ?? null) !== 'KFZ') {
                    continue;
                }

                $callsign = VehicleCallsign::normalize($resource['ressource'] ?? null);

                if ($callsign !== null) {
                    $callsigns[$callsign] = true;
                }
            }
        }
    }
}
