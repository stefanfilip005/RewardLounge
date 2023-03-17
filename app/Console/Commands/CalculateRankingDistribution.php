<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\RankingDistribution;
use App\Models\Ranking;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use stdClass;

class CalculateRankingDistribution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculateRankingDistribution';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the ranking distribution based on the points';

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

        $rankings = array();
        $shifts = Shift::where('start','>=',$yearStart)->where('start','<=',$yearEnd)->get();
        foreach($shifts as $shift){
            if(!isset($rankings[$shift->employeeId])){
                $rankings[$shift->employeeId] = 0;
            }
            $rankings[$shift->employeeId] += $shift->points;
        }
        foreach($rankings as $key => $employee){
            $rankings[$key] = floor($employee);
            if($employee == 0){
                unset($rankings[$key]);
            }
        }

        usort($rankings, function($a, $b){ return $a - $b; });


        $firstPos = floor(count($rankings)/4);
        $secondPos = floor(count($rankings)/4*2);
        $thirdPos = floor(count($rankings)/4*3);

        $limits = array();
        $limits[] = floor($rankings[$firstPos] * 0.33);
        $limits[] = floor($rankings[$firstPos] * 0.66);
        $limits[] = floor($rankings[$firstPos]);

        $limits[] = floor($rankings[$firstPos] + ($rankings[$secondPos] - $rankings[$firstPos]) * 0.33);
        $limits[] = floor($rankings[$firstPos] + ($rankings[$secondPos] - $rankings[$firstPos]) * 0.66);
        $limits[] = floor($rankings[$secondPos]);

        $limits[] = floor($rankings[$secondPos] + ($rankings[$thirdPos] - $rankings[$secondPos]) * 0.33);
        $limits[] = floor($rankings[$secondPos] + ($rankings[$thirdPos] - $rankings[$secondPos]) * 0.66);
        $limits[] = floor($rankings[$thirdPos]);

        $limits[] = floor($rankings[$thirdPos] + ($rankings[count($rankings)-1] - $rankings[$thirdPos]) * 0.33);
        $limits[] = floor($rankings[$thirdPos] + ($rankings[count($rankings)-1] - $rankings[$thirdPos]) * 0.33);

        $limits = array_unique($limits);

        $distincts = array();
        $distincts[] = [
            'year' => $year,
            'limit' => 1,
            'count' => 0
        ];
        foreach($limits as $limit){
            $distincts[] = [
                'year' => $year,
                'limit' => $limit,
                'count' => 0
            ];
        }


        foreach($rankings as $ranking){
            for($i = 0; $i < count($distincts); $i++){
                if($i == count($distincts)-1){
                    if($ranking >= $distincts[$i]['limit']){
                        $distincts[$i]['count']++;
                    }
                }else{
                    if($ranking >= $distincts[$i]['limit'] && $ranking < $distincts[$i+1]['limit']){
                        $distincts[$i]['count']++;
                    }
                }
            }
        }

        RankingDistribution::where('year','=',$year)->delete();
        RankingDistribution::insert($distincts);

    }
}
