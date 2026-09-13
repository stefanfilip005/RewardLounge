<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeShiftStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DFEmployeesController extends Controller
{
    public function __invoke(Request $request, EmployeeShiftStatus $shiftStatus): JsonResponse
    {
        $employees = Employee::where('Status', 'Ehrenamtlich')->get();
        $statuses = $shiftStatus->forEmployees($employees->pluck('remoteId')->all());
        $data = $employees->map(function (Employee $employee) use ($request, $statuses) {
            $employee->self = $employee->remoteId == $request->user()->remoteId;

            // Only the DF response includes live shift data. Shared employee resources stay static.
            return array_merge(
                (new EmployeeResource($employee))->resolve($request),
                $statuses[$employee->remoteId]
            );
        });

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'private, no-store');
    }
}
