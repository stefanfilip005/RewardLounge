<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class GrabCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:grabCourses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects all courses from the NRK API';

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
        $apicall['req'] = 'GETNextKurse';
        $apicall['anz'] = 25;

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
        Course::where('date', '>=', Carbon::now()->toDateString())->delete();


        if(isset($return['data'])){
            $courses = [];
            $courseIds = [];  // Keep track of already processed course IDs to avoid duplicates

            foreach($return['data'] as $row){
                $courseId = $row['ID'];
                if(!in_array($courseId, $courseIds)){
                    $courses[] = array(
                        'course_id' => $courseId,
                        'von' => $row['VON'],
                        'bis' => $row['BIS'],
                        'date' => $row['DATE'],
                        'info' => $row['INFO'] ?? null,  // Use the null coalescing operator to handle non-existing indexes
                        'name' => $row['NAME']
                    );
                    $courseIds[] = $courseId;
                }
            }
            Course::upsert($courses, ['course_id'], ['von', 'bis', 'date', 'info', 'name']);
        }

    }
}
