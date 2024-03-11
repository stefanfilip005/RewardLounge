<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Shift;
use App\Http\Resources\ShiftResource;
use Illuminate\Http\Request;

class ShiftsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shifts = Shift::orderBy('start','asc')->paginate(5000);
        return ShiftResource::collection($shifts);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shift = Shift::findOrFail($id);
        return new ShiftResource($shift);
    }

    public function search(Request $request)
    {

        $query = Shift::query();

        if ($request->has('location') && $request->location != "") {
            $locationMapping = [
                'Hollabrunn' => 38,
                'Haugsdorf' => 39,
            ];
    
            $locationId = $locationMapping[$request->location] ?? null;
    
            if (!$locationId) {
                return response()->json(['message' => 'Invalid location'], 400);
            }
            $locationId = $locationMapping[$request->location];
            $query->where('location', $locationId);
        }
    
        if ($request->has('personalNumber') && $request->personalNumber != "") {
            $query->where('employeeId', $request->personalNumber);
        }
    
        if ($request->has('date') && $request->date != "") {
            $query->whereDate('start', $request->date);
        }
    
        $shifts = $query->orderBy('start','desc')->limit(10)->get();

        return ShiftResource::collection($shifts);
    }
    public function updatePoints(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'overwrittenPoints' => 'required|numeric'
        ]);

        $shift = Shift::findOrFail($request->id);
        $shift->overwrittenPoints = $request->overwrittenPoints;
        $shift->save();

        return response()->json(['message' => 'Shift points updated successfully']);
    }

}
