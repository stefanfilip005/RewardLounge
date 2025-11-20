<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BallSlotResource;
use App\Models\BallSlot;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BallSlotsController extends Controller
{
    public function index(): JsonResponse
    {
        $slots = BallSlot::orderBy('id')->get();
        return response()->json(
            BallSlotResource::collection($slots)
        );
    }



    /**
     * Eingeloggter User trägt sich selbst in einen Slot ein.
     *
     * POST /api/ball-slots/{slot}/self-register
     */
    public function selfRegister(Request $request, int $slotId): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->first();
        if (! $employee) {
            return response()->json([
                'message' => 'Für diesen Benutzer wurde kein Mitarbeiter gefunden.',
            ], 404);
        }

        $ballSlot = BallSlot::findOrFail($slotId);
        if (! empty($ballSlot->helper_name)) {
            return response()->json([
                'message' => 'Dieser Slot ist bereits vergeben.',
            ], 409);
        }

        $ballSlot->mtr_number   = $user->id;
        $ballSlot->helper_name  = trim($employee->firstname . ' ' . $employee->lastname);
        $ballSlot->helper_email = $employee->email ?: null;
        $ballSlot->helper_phone = $employee->phone ?: null;
        $ballSlot->filled_by_user_id = $user->id;
        $ballSlot->filled_at = now();
        $ballSlot->save();

        return response()->json([
            'data'    => $ballSlot->fresh(),
            'message' => 'Slot erfolgreich belegt.',
        ]);
    }
}
