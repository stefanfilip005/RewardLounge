<?php

namespace App\Console\Commands;

use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class GrabShiftsNew extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabShiftsNew';

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
        
        $statusMap = [];

        $employees = array();
        $employeeIds = array();

        $apicall = array();
        $apicall['req'] = 'GetAllMA';
        $apicall['withguests'] = 1;

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
        $return = json_decode($return, true);
        if (isset($return['data']) && is_array($return['data'])) {
            foreach ($return['data'] as $row) {
                if (!isset($row['Personalnr'])) {
                    continue;
                }
                $personalnr = $row['Personalnr'];
                $status     = $row['Status'] ?? null;
                $statusMap[$personalnr] = $status;
            }
        }


        $plans = array();
        $plans['data'] = array();
        $plans['data'][82] = 'Haugsdorf';
        $plans['data'][3316] = 'Hollabrunn (RD)';

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

            //print_r($dutys);




            $apicall = [
                'req' => 'GET_INCODE_PLAN',
                'von' => date('Y-m-d', strtotime('-32 days')),
                'bis' => date("Y-m-d", strtotime('-29 days')),
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
        
// >>> HIER: Response in Datei speichern
$timestamp = date('Ymd_His');
// Optional: von/bis in den Dateinamen aufnehmen, wenn du magst
$from = date('Y-m-d', strtotime('-31 days'));
$to   = date('Y-m-d', strtotime('-29 days'));

$filename = "nrk_plans/plan_{$planId}_{$from}_{$to}_{$timestamp}.json";

// Schön formatiertes JSON speichern
Storage::disk('local')->put(
    $filename,
    json_encode($shiftData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Wenn du magst, temporär noch eine kurze Ausgabe:
echo "Saved GET_INCODE_PLAN for planId {$planId} to storage/app/{$filename}\n";





        }


        return;







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

            if (!empty($dutys['data'])) {
                foreach ($dutys['data'] as &$d) { // Referenz wichtig, damit wir $dutys mutieren
                    if (
                        isset($d['mnr']) && (int)$d['mnr'] === 393305 // nur dieser Mitarbeiter
                        && (!isset($d['artid']) || $d['artid'] === '' || $d['artid'] === null || $d['artid'] === '?')
                    ) {
                        $d['artid'] = 'HB';
                        if (empty($d['art'])) { $d['art'] = 'Hauptberuflich'; }
                    }
                }
                unset($d); // Referenz sauber lösen
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

                        $shiftBegin = Carbon::parse($resource['begin'], 'Europe/Vienna');
                        $shiftEnd   = Carbon::parse($resource['end'], 'Europe/Vienna');

                        $demandType = $this->resolveShiftType($employeeId, $shiftBegin, $shiftEnd, $employeeDutyMap);

                        if (!in_array($resource['typid'] . '_X_' . $demandType, $demandTypeNames)) {
                            if ($demandType === null || $demandType === '' || $demandType === '?') {
                                $demandType = self::UNKNOWN_SHIFT_TYPE;
                            }
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


            foreach ($shiftData['data']['pool'] as $shiftId => $pool) {
                if (!empty($pool['mnr']) && !empty($pool['typid'])) {
                    $employeeId = $pool['mnr'];
                    if(!in_array($employeeId,$employeeIds)){
                        $employees[] = array('remoteId' => $employeeId); //used for upsert later
                        $employeeIds[] = $employeeId;
                    }

                    $shiftBegin = Carbon::parse($pool['begin'], 'Europe/Vienna');
                    $shiftEnd   = Carbon::parse($pool['end'], 'Europe/Vienna');

                    $demandType = $this->resolveShiftType($employeeId, $shiftBegin, $shiftEnd, $employeeDutyMap);

                    if (!in_array($pool['typid'] . '_X_' . $demandType, $demandTypeNames)) {
                        if ($demandType === null || $demandType === '' || $demandType === '?') {
                            $demandType = self::UNKNOWN_SHIFT_TYPE;
                        }
                        $demandTypes[] = array('name' => $pool['typid'], 'shiftType' => $demandType); // Used for upsert
                        $demandTypeNames[] = $pool['typid'] . '_X_' . $demandType; // Custom key
                    }

                    $shift = array(
                        'employeeId' => $employeeId,
                        'start' => Carbon::parse($pool['begin'], 'Europe/Vienna'),
                        'end' => Carbon::parse($pool['end'], 'Europe/Vienna'),
                        'demandType' => $pool['typid'] ?? '',
                        'shiftType' => $demandType,
                        'location' => $planId
                    );
                    $allShifts[] = $shift;
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
