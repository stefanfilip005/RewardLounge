<?php

namespace App\Console\Commands;

use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\FutureOpenShift;
use App\Models\FutureShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabFutureShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabFutureShifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all furute shifts from the NRK API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $validKlasses = array();

        $validKlasses[] = 'DF';

        $validKlasses[] = 'PNEF';
        $validKlasses[] = 'PLNEF';
        $validKlasses[] = 'FNEF';
        $validKlasses[] = 'NFS';
        
        $validKlasses[] = 'PNAW';
        $validKlasses[] = 'FNAW';
        $validKlasses[] = 'FSNAW';
        $validKlasses[] = 'LSRTW';

        $validKlasses[] = 'FRTW';
        $validKlasses[] = 'FRTWC';
        $validKlasses[] = 'SR1';
        $validKlasses[] = 'SR2';
        
        $validKlasses[] = 'LRTWC';
        $validKlasses[] = 'PRS';
        $validKlasses[] = 'PNFS';

        $validKlasses[] = 'FKTW';
        $validKlasses[] = 'PKTW';
        $validKlasses[] = 'FKTWB';
        $validKlasses[] = 'SK1';
        $validKlasses[] = 'SK2';
        $validKlasses[] = 'BKTWB';
        
        
        $validKlasses[] = 'FBKTW';
        
        $validKlasses[] = 'F-BEL';
        $validKlasses[] = 'BEL';
        $validKlasses[] = 'RUFDF';
        $validKlasses[] = 'PDF';


        $plans = array();
        $plans['data'] = array();
        $plans['data'][82] = 'Haugsdorf';
        $plans['data'][3316] = 'Hollabrunn (RD)';
        //$plans['data'][4748] = 'KI-Team Hollabrunn';

        $allShifts = [];

        // Iterate over each plan and make an API call
        foreach ($plans['data'] as $planId => $planName) {
            $apicall = [
                'req' => 'GET_INCODE_PLAN',
                'von' => date('Y-m-d'),
                'bis' => date("Y-m-d", strtotime('+14 days')),
                'OIDOrgEinheit' => $planId,
            ];
        
            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, config('custom.NRKAPISERVER'));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apicall));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'NRK-AUTH: ' . config('custom.NRKAPIKEY'),
                'Content-Type: application/json',
            ]);
            $return = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log('cURL Error: ' . curl_error($ch));
                continue;
            }
            curl_close($ch);
            if (strlen($return) < 5) {
                continue;
            }
        
            $shiftData = json_decode($return, true);
            if (!isset($shiftData['data']['plan']) || !is_array($shiftData['data']['plan'])) {
                continue;
            }
        
            foreach ($shiftData['data']['plan'] as $shiftId => $shift) {
                foreach ($shift['ressources'] as $resource) {
                    if (!empty($resource['mnr']) && !empty($resource['typid']) && in_array($resource['typid'], $validKlasses)) {
                        $allShifts[] = [
                            'shift_id' => $shiftId,
                            'date' => $shift['date'] ?? null,
                            'begin' => $shift['begin'] ?? null,
                            'end' => $shift['end'] ?? null,
                            'vehicle_type' => $shift['typ'] ?? null,
                            'vehicle_type_id' => $shift['typid'] ?? null,
                            'role' => $resource['typ'] ?? null,
                            'role_id' => $resource['typid'] ?? null,
                            'employee_id' => $resource['mnr'],
                            'employee_name' => $resource['name_ma'] ?? null,
                        ];
                    }
                }
            }
        }
        
        // Truncate and insert shifts in chunks
        FutureShift::truncate();
        $chunkSize = 100; 
        $chunks = array_chunk($allShifts, $chunkSize);
        
        foreach ($chunks as $chunk) {
            FutureShift::insert($chunk);
        }
    }
}
