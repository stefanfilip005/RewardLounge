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
    protected $signature = 'command:storeFutureOpenShifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        FutureOpenShift::truncate();

        for ($day = 0; $day <= 7; $day++) {
            $currentDate = Carbon::today()->addDays($day);
            $dateFormatted = $currentDate->format('Y-m-d');
    
            $dailyShifts = FutureShift::where('AbteilungId', 38)
                ->where('IstOptional', 0)
                ->where('ISTForderer', 0)
                ->whereNull('ObjektId')
                ->where('Beginn', 'LIKE', "$dateFormatted%")
                ->where('KlassId', 'NOT LIKE', 'KFZ%')
                ->where('KlassId', 'NOT LIKE', 'ANA')
                ->get();
        
            foreach ($dailyShifts as $shift) {
                FutureOpenShift::create($shift->toArray());
            }

            $dailyShifts = FutureShift::where('AbteilungId', 39)
                ->where('IstOptional', 0)
                ->where('ISTForderer', 0)
                ->whereNull('ObjektId')
                ->where('Beginn', 'LIKE', "$dateFormatted%")
                ->where('KlassId', 'NOT LIKE', 'KFZ%')
                ->where('KlassId', 'NOT LIKE', 'ANA')
                ->get();
        
            foreach ($dailyShifts as $shift) {
                FutureOpenShift::create($shift->toArray());
            }
        }
    }
}
