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

        $validKlasses[] = 'FRTWC';
        $validKlasses[] = 'SR1';
        $validKlasses[] = 'SR2';

        $validKlasses[] = 'FKTW';
        $validKlasses[] = 'FKTWB';
        $validKlasses[] = 'SK1';
        $validKlasses[] = 'SK2';
        

        $validKlasses[] = 'FBKTW';


        /*
        $apicall['req'] = 'GET_INCODE_PLAENE';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,config('custom.NRKAPISERVER'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apicall));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'NRK-AUTH: '.config('custom.NRKAPIKEY'), 'Content-Type:application/json' ));
        $return = curl_exec ($ch);
        if(strlen($return) < 5){
            return;
        }
        $plans = json_decode($return, true);
        if(isset($plans['data'])){
        
        }
        */
        $plans = array();
        $plans['data'] = array();
        $plans['data'][82] = 'Haugsdorf';
        $plans['data'][3316] = 'Hollabrunn (RD)';
        //$plans['data'][4748] = 'KI-Team Hollabrunn';

        $allShifts = [];
        foreach ($plans['data']['plan'] as $shiftId => $shiftData) {
            foreach ($shiftData['ressources'] as $resource) {
                if (!empty($resource['mnr'])) {
                    $shift = [
                        'shift_id' => $shiftId,
                        'date' => $shiftData['date'] ?? null,
                        'begin' => $shiftData['begin'] ?? null,
                        'end' => $shiftData['end'] ?? null,
                        'vehicle_type' => $shiftData['typ'] ?? null,
                        'vehicle_type_id' => $shiftData['typid'] ?? null,
                        'role' => $resource['typ'] ?? null,
                        'role_id' => $resource['typid'] ?? null,
                        'employee_id' => $resource['mnr'],
                        'employee_name' => $resource['name_ma'] ?? null
                    ];
                    $allShifts[] = $shift;
                }
            }
        }
        FutureShift::truncate();
        $chunkSize = 100; 
        $chunks = array_chunk($allShifts, $chunkSize);

        foreach ($chunks as $chunk) {
            FutureShift::insert($chunk);
        }
    }
}
