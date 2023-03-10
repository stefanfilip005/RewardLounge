<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Shift;
use App\Http\Requests\ShiftRequest;
use App\Http\Resources\ShiftResource;

class ShiftsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shifts = Shift::paginate(15);
        return ShiftResource::collection($shifts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ShiftRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShiftRequest $request)
    {
        $shift = new Shift;
		$shift->employeeId = $request->input('employeeId');
		$shift->start = $request->input('start');
		$shift->end = $request->input('end');
		$shift->usage = $request->input('usage');
		$shift->location = $request->input('location');
		$shift->duration = $request->input('duration');
        $shift->save();

        return response()->json($shift, 201);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  ShiftRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ShiftRequest $request, $id)
    {
        $shift = Shift::findOrFail($id);
		$shift->employeeId = $request->input('employeeId');
		$shift->start = $request->input('start');
		$shift->end = $request->input('end');
		$shift->usage = $request->input('usage');
		$shift->location = $request->input('location');
		$shift->duration = $request->input('duration');
        $shift->save();

        return response()->json($shift);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return response()->json(null, 204);
    }
}
