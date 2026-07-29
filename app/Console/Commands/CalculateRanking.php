<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\EmployeesController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateRanking extends Command
{
    private const NEW_YEAR_DELAY_IN_WEEKS = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculateRanking
                            {--year= : The year to calculate the ranking for}';

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
        $requestedYear = $this->option('year');
        $now = Carbon::now('Europe/Vienna');

        if ($requestedYear === null && !self::currentYearIsReady($now)) {
            $this->info(
                sprintf(
                    'Ranking calculation for %d starts on %s.',
                    $now->year,
                    $now->copy()->startOfYear()->addWeeks(self::NEW_YEAR_DELAY_IN_WEEKS)->toDateString()
                )
            );

            return 0;
        }

        if ($requestedYear !== null && !preg_match('/^\d{4}$/', (string) $requestedYear)) {
            $this->error('The --year option must be a four-digit year.');

            return 1;
        }

        $year = $requestedYear === null ? $now->year : (int) $requestedYear;
        EmployeesController::calculateRankings($year);

        return 0;
    }

    public static function currentYearIsReady(Carbon $now): bool
    {
        return $now->gte(
            $now->copy()->startOfYear()->addWeeks(self::NEW_YEAR_DELAY_IN_WEEKS)
        );
    }
}
