<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Support\Facades\Redis;

class PrefillShiftCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:prefill-shifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prefill Redis cache with shifts for all employees for the past 2 years.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $today = Carbon::now();
        $pastTwoYears = $today->clone()->subYears(2);
        $pastTwoYears = $pastTwoYears->greaterThanOrEqualTo(Carbon::parse('2023-01-01')) ? $pastTwoYears : Carbon::parse('2023-01-01');
        $employees = Employee::all();
        foreach($employees as $employee) {
            $employeeId = $employee->remoteId;
            echo "Caching shifts for employee $employee->lastname\n";
            for($year = $pastTwoYears->year; $year <= $today->year; $year++) {
                $cacheKey = "shifts:$employeeId:$year";
                $shifts = Shift::where('employeeId', $employeeId)
                    ->whereYear('start', $year)
                    ->orderBy('start', 'asc')
                    ->get();
                if($shifts->isEmpty()) {
                    continue;
                }
                Redis::setex($cacheKey, 60 * 60 * 24, serialize($shifts));
            }
        }
        return 0;
    }
}