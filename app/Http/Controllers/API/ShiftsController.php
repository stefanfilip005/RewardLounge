<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Shift;
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
        $shifts = Shift::orderBy('start','asc')->paginate(15);
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

}
