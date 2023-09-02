<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\RankingResource;
use App\Http\Resources\RankingDistributionResource;
use App\Http\Resources\ShiftResource;
use App\Models\Ranking;
use App\Models\Shift;
use App\Models\RankingDistribution;
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
                $employees = Employee::where('points','>',0)->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
            }else if($request->sortMode == 1){
                $employees = Employee::where('points','>',0)->orderBy('points','desc')->orderBy('shifts','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
            }else{
                $employees = Employee::where('points','>',0)->orderBy('shifts','desc')->orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
            }
        }else{
            if($request->sortMode == 0){
                $employees = Employee::orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
            }else if($request->sortMode == 1){
                $employees = Employee::orderBy('points','desc')->orderBy('shifts','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
            }else{
                $employees = Employee::orderBy('shifts','desc')->orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(500);
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
        $year = 2023;
        if(Ranking::where('year',$year)->where('remoteId',$request->user()->remoteId)->exists()){
            $ranking = Ranking::where('year',$year)->where('remoteId',$request->user()->remoteId)->first();
        }else{
            $ranking = Ranking::where('year',$year)->orderBy('place','desc')->first();
            $ranking->place = $ranking->place + 1;
            $ranking->pointsForNext = 1;
            $ranking->points = 0;
        }
        return RankingResource::make($ranking);
    }
    public function selfShifts(Request $request){
        $shifts = Shift::where('employeeId',$request->user()->remoteId)->orderBy('start','asc')->paginate(50);
        return ShiftResource::collection($shifts);
    }

    public function rankingDistribution(Request $request){
        $year = 2023;
        $rankingDistribution = RankingDistribution::where('year',$year)->orderBy('limit','asc')->get();
        return RankingDistributionResource::collection($rankingDistribution);
    }

    

}
