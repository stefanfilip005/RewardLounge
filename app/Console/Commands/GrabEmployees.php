<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabEmployees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all employees from the NRK API';

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
        if(isset($return['data'])){
            foreach($return['data'] as $row){
                $employeeId = $row['Personalnr'];
                if(!in_array($employeeId,$employeeIds)){
                    $employees[] = array(
                        'remoteId' => $employeeId,
                        'firstname' => $row['Vorname'],
                        'lastname' => $row['Nachname'],
                        'email' => $row['E-Mail'],
                        'phone' => $row['Mobil'],
                        'Status' => $row['Status'],
                        'BezirksstellenNr' => $row['Bezirksstellen Nr'],
                        'Mitarbeitertyp' => $row['Mitarbeitertyp'],
                        'beurlaubtBis' => $row['beurlaubt bis']
                    );
                    $employeeIds[] = $employeeId;
                }
            }
            Employee::upsert($employees,['remoteId'],['firstname','lastname','email','phone','Status','BezirksstellenNr','Mitarbeitertyp','beurlaubtBis']);
        }

/*
        $employees = Employee::get();
        foreach($employees as $employee){
            // GetMAData
            // MAPicture
            // MAQualifikationen
            // GETMATBereiche


            $apicall = array();
            $apicall['mnr'] = $employee->remoteId;
            $apicall['req'] = 'MAPicture';
    
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
            if(isset($return['data']) && strlen($return['data']) > 500){
                $employee->picture_base64 = $return['data'];
                $employee->save();
            }
            usleep(200000); // 0.2 seconds
        }*/

    }
}
