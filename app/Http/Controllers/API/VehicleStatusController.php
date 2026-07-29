<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NrkVehicleStatusService;
use RuntimeException;

class VehicleStatusController extends Controller
{
    public function index(NrkVehicleStatusService $vehicleStatusService)
    {
        try {
            return response()->json($vehicleStatusService->getStatuses());
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Fahrzeugstatus derzeit nicht verfügbar.',
            ], 502);
        }
    }
}
