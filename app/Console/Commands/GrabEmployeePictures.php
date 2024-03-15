<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabEmployeePictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabEmployeePictures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all pictures of the employees from the NRK API';

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
        $employees = Employee::get();
        foreach($employees as $employee){
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
        }

    }
}
