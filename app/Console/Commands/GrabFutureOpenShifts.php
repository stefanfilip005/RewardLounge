<?php

namespace App\Console\Commands;

use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\FutureOpenShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabFutureOpenShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabFutureOpenShifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all open shifts from the NRK API';

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
        $allShifts = array();

        $apicall = array();
        $apicall['req'] = 'RPS_PLAENE';

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
            foreach($plans['data'] as $plan){
                $apicall['req'] = 'RPS';
                $apicall['von'] = date('Y-m-d');
                $apicall['bis'] = date("Y-m-d", strtotime('+28 days'));
                $apicall['rpsid'] = $plan['id_rps'];
            
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
                $shiftData = json_decode($return, true);
                if(isset($shiftData['data']) && isset($shiftData['data']['plan'])){
                    foreach($shiftData['data']['plan'] as $row){
                        if($row['AbteilungId'] != 38 && $row['AbteilungId'] != 39){
                            continue;
                        }
                        if(!isset($row['ObjektId']) || strlen($row['ObjektId']) == 0){
                            // This shift is still open to take
                            
                            $shift = array(
                                'start' => Carbon::parse($row['Beginn'], 'Europe/Vienna'),
                                'end' => Carbon::parse($row['Ende'], 'Europe/Vienna'),
                                'demandType' => $row['KlassId'],
                                'location' => $row['AbteilungId']
                            );
                            $allShifts[] = $shift;
                        }
                    }
                }
            }
            FutureOpenShift::truncate();
            $chunkSize = 100;
            $chunks = array_chunk($allShifts, $chunkSize);

            foreach ($chunks as $chunk) {
                FutureOpenShift::insert($chunk);
            }
        }
    }
}
