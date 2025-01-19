<?php

namespace App\Console\Commands;

use App\Models\Demandtype;
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

        /*
        Array
        (
            [status] => OK
            [data] => Array
                (
                    [82] => Haugsdorf
                    [1211] => Henry Laden Hollabrunn
                    [1238] => Betreutes Wohnen Hollabrunn I
                    [1239] => Betreutes Wohnen Hollabrunn II
                    [1258] => Team Österreich Tafel Hollabrunn
                    [1311] => Hollabrunn (GSD)
                    [1369] => Betreutes Reisen
                    [1423] => Seniorentreff
                    [1448] => Betreutes Wohnen
                    [1497] => Lese - und Lern Patenschaft
                    [1545] => Krisenintervention
                    [1577] => SvE/Peer
                    [1680] => Team Österreich Tafel
                    [1734] => Henry Laden
                    [1768] => Rufhilfe
                    [1819] => Pflegebehelfeverleih
                    [3000] => Hollabrunn (Verein)
                    [3001] => Verwaltung
                    [3002] => Haugsdorf (Verein)
                    [3003] => Verwaltung
                    [3140] => Besuchs - und Begleitdienst
                    [3316] => Hollabrunn (RD)
                    [4748] => KI-Team Hollabrunn
                    [5522] => Hollabrunn (KHD)
                    [5819] => Migration & Suchdienst
                )

        )
        */


        $employees = array();
        $employeeIds = array();
        $allShifts = array();
        $demandTypes = array();
        $demandTypeNames = array();

        $apicall = array();
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
            foreach($plans['data'] as $key => $plan){
                $startDate = date('Y-m-d', strtotime('-14 days'));
                $minimumDate = '2025-01-01';
                if ($startDate < $minimumDate) {
                    $startDate = $minimumDate;
                }
                $apicall['req'] = 'GET_INCODE_PLAN';
                $apicall['von'] = $startDate;
                $apicall['bis'] = date("Y-m-d", strtotime('-1 days'));
                $apicall['OIDOrgEinheit'] = $key;
            
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

                print_r($shiftData);
                if(isset($shiftData['data']) && isset($shiftData['data']['plan'])){
                    foreach($shiftData['data']['plan'] as $entry){

                        foreach($entry['ressources'] as $ressource){
                            $employeeId = $ressource['mnr'];
                            $shift = array(
                                'employeeId' => $employeeId,
                                'start' => Carbon::parse($row['begin'], 'Europe/Vienna'),
                                'end' => Carbon::parse($row['end'], 'Europe/Vienna'),

                                'demandType' => $row['KlassId'],
                                'shiftType' => $row['DienstartBeschreibung'],
                                'location' => $plan['id_rps']
                            );
                            $allShifts[] = $shift;

                        }





                            if(strlen($row['ObjektId']) > 1){
                                $employeeId = ltrim(substr($row['ObjektId'], 1), '0');

                                if(!in_array($employeeId,$employeeIds)){
                                    $employees[] = array('remoteId' => $employeeId);
                                    $employeeIds[] = $employeeId;
                                }

                                if(!in_array($row['KlassId'] . '_X_' . $row['DienstartBeschreibung'],$demandTypeNames)){
                                    $demandTypes[] = array('name' => $row['KlassId'],'shiftType' => $row['DienstartBeschreibung']);
                                    $demandTypeNames[] = $row['KlassId'] . '_X_' . $row['DienstartBeschreibung'];
                                }
                            }




                    }
                }

            }
            /*
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
            Demandtype::upsert($demandTypes,['name','shiftType']);
            Employee::upsert($employees,['remoteId']);


            // Step 1: Retrieve and store shifts with overwritten points
            $shiftsWithOverwrittenPoints = Shift::where('start', '>=', $lowestDate)
                ->where('end', '<=', $highestDate)
                ->whereNotNull('overwrittenPoints')
                ->get(['employeeId', 'start', 'end', 'demandType', 'shiftType', 'location', 'overwrittenPoints']);

            $temporaryStorage = $shiftsWithOverwrittenPoints->toArray();


            // Step 2: Delete and insert shifts
            Shift::where('start','>=',$lowestDate)->where('end','<=',$highestDate)->delete();
            Shift::insert($allShifts);

            // Step 3: Match and update new shifts
            $newShifts = Shift::where('start', '>=', $lowestDate)->where('end', '<=', $highestDate)->get();

            foreach ($newShifts as $newShift) {
                foreach ($temporaryStorage as $oldShift) {
                    if ($newShift->employeeId == $oldShift['employeeId'] && 
                        $newShift->start == $oldShift['start'] && 
                        $newShift->end == $oldShift['end'] && 
                        $newShift->demandType == $oldShift['demandType'] && 
                        $newShift->shiftType == $oldShift['shiftType'] && 
                        $newShift->location == $oldShift['location']) {
                        // Match found - update overwrittenPoints
                        $newShift->overwrittenPoints = $oldShift['overwrittenPoints'];
                        $newShift->save();
                        break; // Break inner loop if match is found
                    }
                }
            }*/

        }
    }
}
