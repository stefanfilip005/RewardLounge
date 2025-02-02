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
        $employees = array();
        $employeeIds = array();
        $allShifts = array();
        $demandTypes = array();
        $demandTypeNames = array();

        $plans = array();
        $plans['data'] = array();
        $plans['data'][82] = 'Haugsdorf';
        $plans['data'][3316] = 'Hollabrunn (RD)';


        $employeeDutyMap = array();
        foreach ($plans['data'] as $planId => $planName) {

            $apicall = [
                'req' => 'GET_INCODE_DUTYS',
                'von' => date('Y-m-d', strtotime('-14 days')),
                'bis' => date("Y-m-d", strtotime('-1 days')),
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
        
            $dutys = json_decode($return, true);
            if (!isset($dutys['data']) || !is_array($dutys['data'])) {
                continue;
            }

            foreach($dutys['data'] as $duty){
                /*
                [0] => Array
                (
                    [date] => 2025-01-05
                    [begin] => 2025-01-05 06:00:00
                    [end] => 2025-01-06 06:00:00
                    [mnr] => 14273
                    [typ] => DienstfÃ¼hrer
                    [typid] => DF
                    [art] => Doppelverwendung
                    [artid] => DV
                )*/
                if(isset($duty['mnr']) && $duty['mnr'] != null && strlen($duty['mnr']) > 1){
                    if(!isset($employeeDutyMap[$duty['mnr']])){
                        $employeeDutyMap[$duty['mnr']] = array();
                    }
                    $employeeDutyMap[$duty['mnr']][] = $duty;
                }
            }

            $apicall = [
                'req' => 'GET_INCODE_PLAN',
                'von' => date('Y-m-d', strtotime('-14 days')),
                'bis' => date("Y-m-d", strtotime('-1 days')),
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
                    if (!empty($resource['mnr']) && !empty($resource['typid'])) {
                        $employeeId = $resource['mnr'];
                        if(!in_array($employeeId,$employeeIds)){
                            $employees[] = array('remoteId' => $employeeId); //used for upsert later
                            $employeeIds[] = $employeeId;
                        }

                        $demandType = "NONE";
                        if (isset($employeeDutyMap[$employeeId])) {
                            foreach ($employeeDutyMap[$employeeId] as $duty) {
                                $dutyDate = Carbon::parse($duty['begin'])->startOfDay();
                                $shiftStartDate = Carbon::parse($resource['begin'])->startOfDay();
                                $shiftStartPrevDay = $shiftStartDate->copy()->subDay();

                                if (Carbon::parse($duty['begin'])->eq(Carbon::parse($resource['begin']))) {
                                    // Exact match with shift begin
                                    $demandType = $duty['artid'];
                                    break;
                                } elseif ($dutyDate->eq($shiftStartDate)) {
                                    // Match on the same day
                                    $demandType = $duty['artid'];
                                } elseif ($dutyDate->eq($shiftStartPrevDay)) {
                                    // Match one day before
                                    $demandType = $duty['artid'];
                                }
                            }
                        }

                        if (!in_array($resource['typid'] . '_X_' . $demandType, $demandTypeNames)) {
                            $demandTypes[] = array('name' => $resource['typid'], 'shiftType' => $demandType); // Used for upsert
                            $demandTypeNames[] = $resource['typid'] . '_X_' . $demandType; // Custom key
                        }

                        $shift = array(
                            'employeeId' => $employeeId,
                            'start' => Carbon::parse($resource['begin'], 'Europe/Vienna'),
                            'end' => Carbon::parse($resource['end'], 'Europe/Vienna'),
                            'demandType' => $resource['typid'] ?? '',
                            'shiftType' => $demandType,
                            'location' => $planId
                        );
                        $allShifts[] = $shift;
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
        }

    }
}
