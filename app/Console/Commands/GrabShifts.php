<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabShifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all shifts starting yesterday till the last 5 days from the NRK API';

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
        $employees = array();
        $employeeIds = array();
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
                $apicall['von'] = date('Y-m-d', strtotime('-5 days'));
                $apicall['bis'] = date("Y-m-d", strtotime('-1 days'));
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
                        if(strcmp("EA-RKT",$row['DienstartBeschreibung']) == 0){
                            if(strlen($row['ObjektId']) > 1){
                                $employeeId = ltrim(substr($row['ObjektId'], 1), '0');

                                if(!in_array($employeeId,$employeeIds)){
                                    $employees[] = array('remoteId' => $employeeId);
                                    $employeeIds[] = $employeeId;
                                }
                                
                                $shift = array(
                                    'employeeId' => $employeeId,
                                    'start' => Carbon::parse($row['Beginn'], 'Europe/Vienna'),
                                    'end' => Carbon::parse($row['Ende'], 'Europe/Vienna'),
                                    'demandType' => $row['KlassId'],
                                    'location' => $row['AbteilungBezeichnung']
                                );
                                $allShifts[] = $shift;
                            }
                        }
                    }
                }
            }
            $lowestDate = Carbon::now()->addYears(100);
            $highestDate = Carbon::now()->subYears(100);
            foreach($allShifts as $line){
                if($line['start']->lessThan($lowestDate)){
                    $lowestDate = $line['start']->copy();
                }
                if($line['end']->greaterThan($highestDate)){
                    $highestDate = $line['end']->copy();
                }
            }
            Employee::upsert($employees,['remoteId']);
            Shift::where('start','>=',$lowestDate)->where('end','<=',$highestDate)->delete();
            Shift::insert($allShifts);
        }
    }
}
