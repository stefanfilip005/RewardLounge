<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\RankingResource;
use App\Http\Resources\ShiftResource;
use App\Http\Resources\StatisticShiftResource;
use App\Models\Ranking;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employees = [];
        if($request->filled('hasPoints')){
            if($request->sortMode == 0){
                $employees = Employee::where('points','>',0)->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }else if($request->sortMode == 1){
                $employees = Employee::where('points','>',0)->orderBy('points','desc')->orderBy('shifts','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }else{
                $employees = Employee::where('points','>',0)->orderBy('shifts','desc')->orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }
        }else{
            if($request->sortMode == 0){
                $employees = Employee::orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }else if($request->sortMode == 1){
                $employees = Employee::orderBy('points','desc')->orderBy('shifts','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }else{
                $employees = Employee::orderBy('shifts','desc')->orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(10000);
            }
        }
        return EmployeeResource::collection($employees);
    }

    public function teamEmployees(Request $request)
    {
        $employees = Employee::where('isAdministrator',true)->orWhere('isModerator',true)->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->select('email','firstname','lastname','isAdministrator','isModerator')->paginate(500);
        return EmployeeResource::collection($employees);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return new EmployeeResource($employee);
    }

    public function userProfile(Request $request){
        return EmployeeResource::make($request->user());
    }

    public function selfRanking(Request $request){
        $userID = $request->user()->remoteId;
        //$userID = 5518;
        //$userID = 38128;//228242;
        $rankings = collect();
        $locations = [null, 38, 39]; // Array of locations including null

        foreach ([2023, 2024] as $year) {
            foreach ($locations as $location) {
                // Modify the query to consider location
                $ranking = Ranking::where('year', $year)
                                    ->where('remoteId', $userID)
                                    ->when($location, function ($query, $location) {
                                        return $query->where('location', $location);
                                    }, function ($query) {
                                        return $query->whereNull('location');
                                    })
                                    ->first();
    
                if (!$ranking) {
                    // If no ranking found, replicate the last ranking for this year and location
                    $lastRanking = Ranking::where('year', $year)
                                            ->when($location, function ($query, $location) {
                                                return $query->where('location', $location);
                                            }, function ($query) {
                                                return $query->whereNull('location');
                                            })
                                            ->orderBy('place', 'desc')
                                            ->first();
                    
                    if ($lastRanking) {
                        $ranking = $lastRanking->replicate();
                        $ranking->place = $ranking->place + 1;
                        $ranking->pointsForNext = 1;
                        $ranking->points = 0;
                        $ranking->remoteId = $userID;
                    } else {
                        // Handle the case where there are no rankings at all for this year and location
                        $ranking = new Ranking([
                            'year' => $year,
                            'remoteId' => $userID,
                            'location' => $location,
                            'place' => 1,
                            'pointsForNext' => 0,
                            'points' => 0
                        ]);
                    }
                }
                $rankings->push($ranking);
            }
        }
        return RankingResource::collection($rankings);
    }
    public function selfShifts(Request $request){
        $userID = $request->user()->remoteId;
        //$userID = 228242;
        if(isset($request->year)) {
            $year = $request->year;
            $shifts = Shift::where('employeeId',$userID)->whereYear('start', $year)->orderBy('start','asc')->paginate(5000);
            //$shifts = Shift::where('employeeId',228242)->whereYear('start', $year)->orderBy('start','asc')->paginate(5000);
        }
        return ShiftResource::collection($shifts);
    }    
    
    
    public function futureShifts(Request $request){
        $apicall = array();
		
		$apicall['req'] = 'GETNextDienste';
        $apicall['mnr'] = $request->user()->remoteId;
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,config('custom.NRKAPISERVER'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apicall));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'NRK-AUTH: '.config('custom.NRKAPIKEY'), 'Content-Type:application/json' ));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			curl_close($ch);
			return json_encode([]);
		}

		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $response;
	}

    public function latestShifts(Request $request){
        $shifts = Shift::where('employeeId',$request->user()->remoteId)->orderBy('start','desc')->paginate(25);
        return ShiftResource::collection($shifts);
    }


    public function shifts(Request $request){
        $shifts = null;
        if(isset($request->year)) {
            $year = $request->year;
            $shifts = Shift::whereIn('location',[38,39])
                ->whereYear('start', $year)
                ->where('demandType', 'NOT LIKE', 'KFZ%')
                ->get();
        }
        return ShiftResource::collection($shifts);
    }
    public function shiftStatistics(Request $request){
        $shifts = null;
        if(isset($request->year)) {
            $year = $request->year;
            $shifts = Shift::whereIn('location',[38,39])
                ->whereYear('start', $year)
                ->where('demandType', 'NOT LIKE', 'KFZ%')
                ->get();
        }
        return StatisticShiftResource::collection($shifts);
    }
    
    
    /**
     * Refactor this method later into an own controller
     */
    public static function calculateRankings($year){
        $yearStart = Carbon::create($year,1,1,0,0,0,"Europe/Vienna");
        $yearEnd = Carbon::create($year+1,1,1,0,0,0,"Europe/Vienna");

        $employees = array();
        $employeesHollabrunn = array();
        $employeesHaugsdorf = array();
        $shifts = Shift::where('start','>=',$yearStart)->where('start','<',$yearEnd)->get();
        foreach($shifts as $shift){
            if(!isset($employees[$shift->employeeId])){
                $employees[$shift->employeeId] = [
                    'remoteId' => $shift->employeeId,
                    'place' => 0,
                    'pointsForNext' => 0,
                    'points' => 0,
                    'location' => null,
                    'year' => $year
                ];
            }
            if($shift->overwrittenPoints != null){
                $employees[$shift->employeeId]['points'] += $shift->overwrittenPoints;
            }else{
                $employees[$shift->employeeId]['points'] += $shift->points;
            }

            if($shift->location == 38){
                if(!isset($employeesHollabrunn[$shift->employeeId])){
                    $employeesHollabrunn[$shift->employeeId] = [
                        'remoteId' => $shift->employeeId,
                        'place' => 0,
                        'pointsForNext' => 0,
                        'points' => 0,
                        'location' => 38,
                        'year' => $year
                    ];
                }
                if($shift->overwrittenPoints != null){
                    $employeesHollabrunn[$shift->employeeId]['points'] += $shift->overwrittenPoints;
                }else{
                    $employeesHollabrunn[$shift->employeeId]['points'] += $shift->points;
                }
            }

            if($shift->location == 39){
                if(!isset($employeesHaugsdorf[$shift->employeeId])){
                    $employeesHaugsdorf[$shift->employeeId] = [
                        'remoteId' => $shift->employeeId,
                        'place' => 0,
                        'pointsForNext' => 0,
                        'points' => 0,
                        'location' => 39,
                        'year' => $year
                    ];
                }
                if($shift->overwrittenPoints != null){
                    $employeesHaugsdorf[$shift->employeeId]['points'] += $shift->overwrittenPoints;
                }else{
                    $employeesHaugsdorf[$shift->employeeId]['points'] += $shift->points;
                }
            }
        }
        foreach($employees as $key => $employee){
            $employees[$key]['points'] = floor($employee['points']);
            if($employee['points'] == 0){
                unset($employees[$key]);
            }
        }
        usort($employees, function($a, $b){ return $b['points'] - $a['points']; });

        foreach($employeesHollabrunn as $key => $employee){
            $employeesHollabrunn[$key]['points'] = floor($employee['points']);
            if($employee['points'] == 0){
                unset($employeesHollabrunn[$key]);
            }
        }
        usort($employeesHollabrunn, function($a, $b){ return $b['points'] - $a['points']; });

        foreach($employeesHaugsdorf as $key => $employee){
            $employeesHaugsdorf[$key]['points'] = floor($employee['points']);
            if($employee['points'] == 0){
                unset($employeesHaugsdorf[$key]);
            }
        }
        usort($employeesHaugsdorf, function($a, $b){ return $b['points'] - $a['points']; });


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
            Ranking::where('year',$year)->whereNull('location')->delete();
            Ranking::upsert($employees,['year','remoteId','location'],['place','points','pointsForNext']);
        }



        $previousHighscore = PHP_INT_MAX;
        $place = 1;
        $platzierungCounter = 1;
        if(count($employeesHollabrunn) > 2){
            for( $i = 0 ; $i < count($employeesHollabrunn) ; $i++ ){
                if($i == 0){
                }else if($i >= count($employeesHollabrunn)-1){
                    if($employeesHollabrunn[$i]['points'] == $employeesHollabrunn[$i-1]['points']){
                        $employeesHollabrunn[$i]['pointsForNext'] = 1;
                    }else{
                        $employeesHollabrunn[$i]['pointsForNext'] = $employeesHollabrunn[$i-1]['points'] - $employeesHollabrunn[$i]['points'];
                    }
                }else{
                    if($employeesHollabrunn[$i]['points'] == $employeesHollabrunn[$i-1]['points'] || $employeesHollabrunn[$i]['points'] == $employeesHollabrunn[$i+1]['points']){
                        $employeesHollabrunn[$i]['pointsForNext'] = 1;
                    }else{
                        $employeesHollabrunn[$i]['pointsForNext'] = $employeesHollabrunn[$i-1]['points'] - $employeesHollabrunn[$i]['points'];
                    }
                }
    
                $employeesHollabrunn[$i]['place'] = $place;
                if($previousHighscore != $employeesHollabrunn[$i]['points']){
                    $place = $platzierungCounter;
                    $employeesHollabrunn[$i]['place'] = $place;
                    $previousHighscore = $employeesHollabrunn[$i]['points'];
                }
                $platzierungCounter++;
            }
            Ranking::where('year',$year)->where('location', 38)->delete();
            Ranking::upsert($employeesHollabrunn,['year','remoteId','location'],['place','points','pointsForNext']);
        }




        $previousHighscore = PHP_INT_MAX;
        $place = 1;
        $platzierungCounter = 1;
        if(count($employeesHaugsdorf) > 2){
            for( $i = 0 ; $i < count($employeesHaugsdorf) ; $i++ ){
                if($i == 0){
                }else if($i >= count($employeesHaugsdorf)-1){
                    if($employeesHaugsdorf[$i]['points'] == $employeesHaugsdorf[$i-1]['points']){
                        $employeesHaugsdorf[$i]['pointsForNext'] = 1;
                    }else{
                        $employeesHaugsdorf[$i]['pointsForNext'] = $employeesHaugsdorf[$i-1]['points'] - $employeesHaugsdorf[$i]['points'];
                    }
                }else{
                    if($employeesHaugsdorf[$i]['points'] == $employeesHaugsdorf[$i-1]['points'] || $employeesHaugsdorf[$i]['points'] == $employeesHaugsdorf[$i+1]['points']){
                        $employeesHaugsdorf[$i]['pointsForNext'] = 1;
                    }else{
                        $employeesHaugsdorf[$i]['pointsForNext'] = $employeesHaugsdorf[$i-1]['points'] - $employeesHaugsdorf[$i]['points'];
                    }
                }
    
                $employeesHaugsdorf[$i]['place'] = $place;
                if($previousHighscore != $employeesHaugsdorf[$i]['points']){
                    $place = $platzierungCounter;
                    $employeesHaugsdorf[$i]['place'] = $place;
                    $previousHighscore = $employeesHaugsdorf[$i]['points'];
                }
                $platzierungCounter++;
            }
            Ranking::where('year',$year)->where('location', 39)->delete();
            Ranking::upsert($employeesHaugsdorf,['year','remoteId','location'],['place','points','pointsForNext']);
        }







    }

}
