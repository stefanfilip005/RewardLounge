<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
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
            $employees = Employee::where('points','>',0)->orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
        }else{
            $employees = Employee::orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
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
}
