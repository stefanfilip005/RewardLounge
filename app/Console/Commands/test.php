<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use stdClass;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

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
        exit;
        $filename = 'Punktesystem_2023.CSV';
        if (Storage::exists($filename)) {
            $contents = Storage::get($filename);
            $lines = explode(PHP_EOL, $contents);
            if(count($lines) > 1){
                $colEmployeeId = -1;
                $colResource = -1;
                $colType = -1;
                $colLocation = -1;
                $colStart = -1;
                $colEnd = -1;
                $columnsLength = -1;
                $this->parseFirstLine($colEmployeeId, $colResource, $colType, $colLocation, $colStart, $colEnd, $columnsLength, $lines[0]);
                array_shift($lines);
    
                // Find the range which was imported in the CSV and delete all stores rows in this range
                $lowestDate = Carbon::now()->addYears(100);
                $highestDate = Carbon::now()->subYears(100);
                $this->findDateRange($lowestDate,$highestDate,$lines,$colResource,$colType,$colStart,$colEnd,$columnsLength);
                Shift::where('start','>=',$lowestDate)->where('end','<=',$highestDate)->delete();
    
                $employees = array();
                $inserts = array();
                foreach($lines as $line){
                    $parts = explode(",", $line);
                    if($columnsLength == count($parts)){
                        if(strcmp($parts[$colType],"EA-RKT") == 0){
                            if(strlen($parts[$colResource]) > 0){
                                $employeeId = ltrim(substr($parts[$colEmployeeId], 1), '0');
                                $employee = array(
                                    'remoteId' => $employeeId
                                );
                                $employees[] = $employee;

                                $start = Carbon::parse($parts[$colStart]);
                                $end = Carbon::parse($parts[$colEnd]);
                                $shift = array(
                                    'employeeId' => $employeeId,
                                    'shiftType' => "EA-RKT",
                                    'start' => $start,
                                    'end' => $end,
                                    'demandType' => $parts[$colResource],
                                    'location' => ($parts[$colLocation] == "Hollabrunn") ? 38 : 39
                                );
                                $inserts[] = $shift;
                            }
                        }
                    }
                }
                Employee::upsert($employees,['remoteId']);
                Shift::insert($inserts);
            }
        }
    }

    private function parseFirstLine(&$colEmployeeId, &$colResource, &$colType, &$colLocation, &$colStart, &$colEnd, &$columnsLength, $line){
        $parts = explode(",", $line);
        $columnsLength = count($parts);
        for($i = 0; $i < count($parts); $i++){
            switch($parts[$i]){
                case 'Objekt ID':
                    $colEmployeeId = $i;
                    break;
                case 'Verwendung':
                    $colResource = $i;
                    break;
                case 'Dienstart':
                    $colType = $i;
                    break;
                case 'Abt.':
                    $colLocation = $i;
                    break;
                case 'Beginn':
                    $colStart = $i;
                    break;
                case 'Ende':
                    $colEnd = $i;
                    break;
            }
        }
    }

    private function findDateRange(&$lowestDate,&$highestDate,$lines,$colResource,$colType,$colStart,$colEnd,$columnsLength){
        $lowestDate = Carbon::now()->addYears(100);
        $highestDate = Carbon::now()->subYears(100);
        foreach($lines as $line){
            $parts = explode(",", $line);
            if($columnsLength == count($parts)){
                if(strcmp($parts[$colType],"EA-RKT") == 0){
                    if(strlen($parts[$colResource]) > 0){
                        $start = Carbon::parse($parts[$colStart]);
                        $end = Carbon::parse($parts[$colEnd]);
                        if($start->lessThan($lowestDate)){
                            $lowestDate = $start;
                        }
                        if($end->greaterThan($highestDate)){
                            $highestDate = $end;
                        }
                    }
                }
            }
        }
    }
}
