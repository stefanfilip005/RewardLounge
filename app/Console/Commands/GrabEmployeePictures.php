<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\EmployeePictureStorage;
use Illuminate\Console\Command;

class GrabEmployeePictures extends Command
{
    /**
     * NRK-Hosting permits at most 60 requests per minute per API key.
     * Keep headroom for interactive requests made by application users.
     */
    private const NRK_REQUEST_DELAY_MICROSECONDS = 2000000;

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
    public function handle(EmployeePictureStorage $pictureStorage)
    {
        $employees = Employee::get(['remoteId']);
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
            curl_close($ch);
            if(!is_string($return) || strlen($return) < 5){
                $this->warn("Foto für Mitarbeiter {$employee->remoteId} konnte nicht abgerufen werden.");
                usleep(self::NRK_REQUEST_DELAY_MICROSECONDS);
                continue;
            }
            $return = json_decode($return, true);
            if(isset($return['data']) && is_string($return['data']) && strlen($return['data']) > 500){
                if (!$pictureStorage->putBase64($employee->remoteId, $return['data'])) {
                    $this->warn("NRK lieferte für Mitarbeiter {$employee->remoteId} kein gültiges Foto.");
                }
            }
            // Two seconds means at most 30 requests/minute for this import.
            usleep(self::NRK_REQUEST_DELAY_MICROSECONDS);
        }

        return 0;
    }
}
