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
        $demandtypes = Demandtype::where('name', 'not like', '%_AMB')
            ->where('name', 'not like', 'KFZ%')
            ->where('name', 'not like', 'MA')
            ->where('name', 'not like', 'KI%')
            ->where('shiftType', 'not like', 'HA%')
            ->where('shiftType', 'not like', 'FSJ%')
            ->where('shiftType', 'not like', 'ZD%')
            ->where('shiftType', 'not like', 'EX-RKT')
            ->where('shiftType', 'not like', 'EA-GSD')
            ->orderBy('shiftType','asc')->orderBy('name','asc')->paginate(5000);
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
		$demandtype->useMultiplicator = $request->input('useMultiplicator');
        $demandtype->save();

        return response()->json($demandtype);
    }

}
