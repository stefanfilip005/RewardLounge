<?php

namespace App\Console\Commands;

use App\Models\FutureShift;
use App\Models\Vehicle;
use App\Services\NrkVehicleStatusService;
use App\Support\VehicleCallsign;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GrabFutureShifts extends Command
{
    protected $signature = 'command:grabFutureShifts';
    protected $description = 'Collects future employee shifts from the NRK API';

    public function handle()
    {
        $allShifts = [];
        $vehicleCallsigns = [];
        $today = Carbon::now('Europe/Vienna')->startOfDay();
        $until = $today->copy()->addDays(max(1, (int) config('custom.NRK_FUTURE_SHIFT_DAYS', 90)));

        try {
            $plans = config('custom.NRK_PLAN_IDS', [82, 3316]);
            if (empty($plans)) {
                throw new RuntimeException('No NRK plans configured.');
            }
            // Include overnight shifts and any longer shift still in progress.
            $firstDay = $today->copy()->subDay();
            $ongoingBegin = FutureShift::where('end', '>', $today->format('Y-m-d H:i:s'))->min('begin');
            if ($ongoingBegin) {
                $firstDay = $firstDay->min(Carbon::parse($ongoingBegin, 'Europe/Vienna')->startOfDay());
            }
            foreach ($plans as $planId) {
                // Small, non-overlapping windows avoid requesting one oversized plan.
                for ($from = $firstDay->copy(); $from->lte($until); $from = $to->copy()->addDay()) {
                    $to = $from->copy()->addDays(13)->min($until);
                    $response = Http::withHeaders(['NRK-AUTH' => config('custom.NRKAPIKEY')])
                        ->acceptJson()->timeout(60)->post(config('custom.NRKAPISERVER'), [
                            'req' => 'GET_INCODE_PLAN',
                            'von' => $from->toDateString(),
                            'bis' => $to->toDateString(),
                            'OIDOrgEinheit' => $planId,
                        ]);
                    $response->throw();
                    $data = $response->json();
                    if ((isset($data['status']) && $data['status'] !== 'OK')
                        || !isset($data['data']['plan']) || !is_array($data['data']['plan'])
                        || (isset($data['data']['pool']) && !is_array($data['data']['pool']))) {
                        throw new RuntimeException('Invalid NRK plan response.');
                    }
                    foreach ($data['data']['plan'] as $shiftId => $shift) {
                        if (!isset($shift['ressources']) || !is_array($shift['ressources'])) {
                            throw new RuntimeException('Invalid NRK resources.');
                        }
                        foreach ($shift['ressources'] as $resource) {
                            if (($resource['typid'] ?? null) === 'KFZ') {
                                $callsign = VehicleCallsign::normalize($resource['ressource'] ?? null);
                                if ($callsign !== null) {
                                    $vehicleCallsigns[$callsign] = true;
                                }
                                continue;
                            }
                            if (!empty($resource['mnr'])) {
                                $allShifts[] = $this->employeeShift($shiftId, $shift, $resource);
                            }
                        }
                    }
                    foreach ($data['data']['pool'] ?? [] as $shiftId => $resource) {
                        if (!empty($resource['mnr']) && ($resource['typid'] ?? null) !== 'KFZ') {
                            $allShifts[] = $this->employeeShift($shiftId, [], $resource);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $this->error('Future shift import failed. Previous shifts were preserved.');
            return 1;
        }

        if (!empty($vehicleCallsigns)) {
            $seenAt = now();
            $vehicleRows = [];
            $activeCallsigns = Vehicle::query()
                ->where('active', true)
                ->whereIn('funkkennung', array_keys($vehicleCallsigns))
                ->pluck('funkkennung')
                ->all();
            $catalogChanged = count($activeCallsigns) !== count($vehicleCallsigns);

            foreach (array_keys($vehicleCallsigns) as $callsign) {
                $vehicleRows[] = [
                    'funkkennung' => $callsign,
                    'active' => true,
                    'last_seen_at' => $seenAt,
                    'created_at' => $seenAt,
                    'updated_at' => $seenAt,
                ];
            }

            Vehicle::upsert(
                $vehicleRows,
                ['funkkennung'],
                ['active', 'last_seen_at', 'updated_at']
            );

            if ($catalogChanged) {
                NrkVehicleStatusService::clearCache();
            }
        }

        try {
            DB::transaction(function () use ($allShifts) {
                // DELETE is transactional; MySQL TRUNCATE would commit immediately.
                FutureShift::query()->delete();
                foreach (array_chunk($allShifts, 100) as $chunk) {
                    FutureShift::insert($chunk);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            $this->error('Could not save future shifts. Previous shifts were preserved.');
            return 1;
        }
        return 0;
    }

    private function employeeShift($shiftId, array $shift, array $resource): array
    {
        $begin = $resource['begin'] ?? $shift['begin'] ?? null;
        $end = $resource['end'] ?? $shift['end'] ?? null;
        if (empty($begin) || empty($end) || empty($resource['typid'])) {
            throw new RuntimeException('Missing employee shift times or role.');
        }
        $begin = Carbon::parse($begin, 'Europe/Vienna')->setTimezone('Europe/Vienna');
        $end = Carbon::parse($end, 'Europe/Vienna')->setTimezone('Europe/Vienna');
        if ($end->lte($begin)) {
            throw new RuntimeException('Invalid employee shift interval.');
        }
        return [
            'shift_id' => (string) $shiftId,
            'date' => $begin->toDateString(),
            'begin' => $begin->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
            'vehicle_type' => $shift['typ'] ?? '',
            'vehicle_type_id' => $shift['typid'] ?? '',
            'role' => $resource['typ'] ?? '',
            'role_id' => $resource['typid'],
            'employee_id' => $resource['mnr'],
            'employee_name' => $resource['name_ma'] ?? null,
        ];
    }
}
