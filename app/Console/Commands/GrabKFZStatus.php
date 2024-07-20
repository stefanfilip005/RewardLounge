<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabKFZStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabKFZStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gibt den Status und das angemeldete Personal des KFZ laut Webansicht von 144 Notruf Niederösterreich aus: (1h Zeitverzögert)';

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

        $funkkennungen = [
            '57-001',
            '57-002',
            '57-010',
            '57-012',
            '57-014',
            '57-020',
            '57-022',
            '57-023',
            '57-024',
            '57-025',
            '57-026',
            '57-027',
            '57-041',
            '57-042',
            '57-099',
            '57-119',
        ];

        foreach($funkkennungen as $kennung){
            $apicall = array();
            $apicall['req'] = 'KFZStatus';
            $apicall['funkkennung'] = $kennung;
    
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
    
            print_r($return);
        }
    }
}
