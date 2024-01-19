<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\EmployeesController;
use App\Models\Employee;
use App\Models\Ranking;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use stdClass;

class CalculateRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculateRanking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the ranking based on the points';

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
        EmployeesController::calculateRankings($year);
        $year = 2024;
        EmployeesController::calculateRankings($year);
    }
}
