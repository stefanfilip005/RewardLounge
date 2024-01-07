<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Demandtype;
use App\Http\Requests\DemandtypeRequest;
use App\Http\Resources\DemandtypeResource;
use Illuminate\Support\Facades\DB;

class DemandtypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $demandtypes = Demandtype::orderBy('shiftType','asc')->orderBy('name','asc')->paginate(5000);
        return DemandtypeResource::collection($demandtypes);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $demandtype = Demandtype::findOrFail($id);
        return new DemandtypeResource($demandtype);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  DemandtypeRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DemandtypeRequest $request, $id)
    {
        $demandtype = Demandtype::findOrFail($id);
		$demandtype->name = $request->input('name');
		$demandtype->shiftType = $request->input('shiftType');
		$demandtype->description = $request->input('description');
		$demandtype->pointsPerMinute = $request->input('pointsPerMinute');
		$demandtype->pointsPerShift = $request->input('pointsPerShift');
        $demandtype->save();

        return response()->json($demandtype);
    }

}
