<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NrkVehicleStatusService;
use Illuminate\Http\Request;
use RuntimeException;

class VehicleStatusController extends Controller
{
    public function index(Request $request, NrkVehicleStatusService $vehicleStatusService)
    {
        try {
            return response()->json(
                $vehicleStatusService->getStatuses($request->header('Authorization'))
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Fahrzeugstatus derzeit nicht verfügbar.',
            ], 502);
        }
    }
}
