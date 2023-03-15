<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::orderBy('lastname','asc')->orderBy('firstname','asc')->orderBy('remoteId','asc')->paginate(50);
        return EmployeeResource::collection($employees);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  EmployeeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request)
    {
        $employee = new Employee;
		$employee->remoteId = $request->input('remoteId');
		$employee->firstname = $request->input('firstname');
		$employee->lastname = $request->input('lastname');
		$employee->email = $request->input('email');
		$employee->phone = $request->input('phone');
		$employee->points = $request->input('points');
		$employee->lastPointCalculation = $request->input('lastPointCalculation');
        $employee->save();

        return response()->json($employee, 201);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  EmployeeRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);
		$employee->remoteId = $request->input('remoteId');
		$employee->firstname = $request->input('firstname');
		$employee->lastname = $request->input('lastname');
		$employee->email = $request->input('email');
		$employee->phone = $request->input('phone');
		$employee->points = $request->input('points');
		$employee->lastPointCalculation = $request->input('lastPointCalculation');
        $employee->save();

        return response()->json($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(null, 204);
    }
}
