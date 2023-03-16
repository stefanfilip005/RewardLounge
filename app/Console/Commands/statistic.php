<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Ranking;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use stdClass;

class statistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:statistic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $year = 2023;

        $yearStart = Carbon::create(2023,1,1,0,0,0,"Europe/Vienna");
        $yearEnd = Carbon::create($year+1,1,1,0,0,0,"Europe/Vienna");

        $employees = array();
        $shifts = Shift::where('start','>=',$yearStart)->where('start','<=',$yearEnd)->get();
        foreach($shifts as $shift){
            if(!isset($employees[$shift->employeeId])){
                $employees[$shift->employeeId] = [
                    'remoteId' => $shift->employeeId,
                    'place' => 0,
                    'pointsForNext' => 0,
                    'points' => 0,
                    'year' => $year
                ];
            }
            $employees[$shift->employeeId]['points'] += $shift->points;
        }
        foreach($employees as $key => $employee){
            $employees[$key]['points'] = floor($employee['points']);
            if($employee['points'] == 0){
                unset($employees[$key]);
            }
        }

        usort($employees, function($a, $b){ return $b['points'] - $a['points']; });

        $previousHighscore = PHP_INT_MAX;
        $place = 1;
        $platzierungCounter = 1;

        if(count($employees) > 2){
            for( $i = 0 ; $i < count($employees) ; $i++ ){
                if($i == 0){
                }else if($i >= count($employees)-1){
                    if($employees[$i]['points'] == $employees[$i-1]['points']){
                        $employees[$i]['pointsForNext'] = 1;
                    }else{
                        $employees[$i]['pointsForNext'] = $employees[$i-1]['points'] - $employees[$i]['points'];
                    }
                }else{
                    if($employees[$i]['points'] == $employees[$i-1]['points'] || $employees[$i]['points'] == $employees[$i+1]['points']){
                        $employees[$i]['pointsForNext'] = 1;
                    }else{
                        $employees[$i]['pointsForNext'] = $employees[$i-1]['points'] - $employees[$i]['points'];
                    }
                }
    
                $employees[$i]['place'] = $place;
                if($previousHighscore != $employees[$i]['points']){
                    $place = $platzierungCounter;
                    $employees[$i]['place'] = $place;
                    $previousHighscore = $employees[$i]['points'];
                }
                $platzierungCounter++;
            }
            Ranking::where('year',$year)->delete();
            Ranking::upsert($employees,['year','remoteId'],['place','points','pointsForNext']);
        }
    }
}
