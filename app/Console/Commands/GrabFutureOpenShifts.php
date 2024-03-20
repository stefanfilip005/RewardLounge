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

        $validKlasses = array();
        //DF
        $validKlasses[] = 'DF1';

        //NEF
        $validKlasses[] = 'FNB_NEF';

        //RTW
        $validKlasses[] = 'FRB';
        $validKlasses[] = 'FRC';
        $validKlasses[] = 'SR1';

        //KTW
        $validKlasses[] = 'FKB';
        $validKlasses[] = 'FKC';
        $validKlasses[] = 'FKB-B';
        $validKlasses[] = 'SK1';

        //BKTW
        $validKlasses[] = 'FBB';

        
        

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
                $apicall['bis'] = date("Y-m-d", strtotime('+14 days'));
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
                        $shift = [
                            'Teil' => $row['Teil'] ?? null,
                            'Verwendung' => $row['Verwendung'] ?? null,
                            'Schicht' => $row['Schicht'] ?? null,
                            'RemoteId' => $row['Id'] ?? null,
                            'KlassId' => $row['KlassId'] ?? null,
                            'IstVollst' => $row['IstVollst'] ?? null,
                            'Datum' => $row['Datum'] ?? null,
                            'Beginn' => $row['Beginn'] ?? null,
                            'Ende' => $row['Ende'] ?? null,
                            'PoolBeginn' => $row['PoolBeginn'] ?? null,
                            'PoolEnde' => $row['PoolEnde'] ?? null,
                            'Bezeichnung' => $row['Bezeichnung'] ?? null,
                            'ObjektId' => $row['ObjektId'] ?? null,
                            'ObjektBezeichnung1' => $row['ObjektBezeichnung1'] ?? null,
                            'ObjektBezeichnung2' => $row['ObjektBezeichnung2'] ?? null,
                            'ObjektInfo' => $row['ObjektInfo'] ?? null,
                            'PlanInfo' => $row['PlanInfo'] ?? null,
                            'IstForderer' => $row['IstForderer'] ?? null,
                            'VaterId' => $row['VaterId'] ?? null,
                            'IstOptional' => $row['IstOptional'] ?? null,
                            'PoolId' => $row['PoolId'] ?? null,
                            'PoolTeil' => $row['PoolTeil'] ?? null,
                            'DienstartId' => $row['DienstartId'] ?? null,
                            'DienstartBeschreibung' => $row['DienstartBeschreibung'] ?? null,
                            'ChgUserAnzeigename' => $row['ChgUserAnzeigename'] ?? null,
                            'ChgUserLoginname' => $row['ChgUserLoginname'] ?? null,
                            'ChgDate' => $row['ChgDate'] ?? null,
                            'AbteilungId' => $row['AbteilungId'] ?? null,
                            'AbteilungBezeichnung' => $row['AbteilungBezeichnung'] ?? null,
                            'AbteilungKZ' => $row['AbteilungKZ'] ?? null,
                            'Info' => $row['Info'] ?? null,
                            'TimeStamp' => $row['TimeStamp'] ?? null,
                            'Processed' => $row['Processed'] ?? null,
                            'MessageSent' => $row['MessageSent'] ?? null,
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
}
