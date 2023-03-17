<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\RankingResource;
use App\Http\Resources\ShiftResource;
use App\Models\Ranking;
use App\Models\Shift;
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
                $employees = Employee::where('points','>',0)->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
            }else{
                $employees = Employee::where('points','>',0)->orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
            }
        }else{
            if($request->sortMode == 0){
                $employees = Employee::orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
            }else{
                $employees = Employee::orderBy('points','desc')->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
            }
        }
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
        $remoteId = 116931;
        $year = 2023;
        if(Ranking::where('year',$year)->where('remoteId',$remoteId)->exists()){
            $ranking = Ranking::where('year',$year)->where('remoteId',$remoteId)->first();
        }else{
            $ranking = Ranking::where('year',$year)->orderBy('place','desc')->first();
            $ranking->place = $ranking->place + 1;
            $ranking->pointsForNext = 1;
            $ranking->points = 0;
        }
        return RankingResource::make($ranking);
    }
    public function selfShifts(Request $request){
        $remoteId = 116931;
        $shifts = Shift::where('employeeId',$remoteId)->orderBy('start','asc')->paginate(15);
        return ShiftResource::collection($shifts);
    }

}
