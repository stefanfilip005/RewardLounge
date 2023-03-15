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
        $demandtypes = Demandtype::paginate(15);
        return DemandtypeResource::collection($demandtypes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  DemandtypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DemandtypeRequest $request)
    {
        $demandtype = new Demandtype;
		$demandtype->name = $request->input('name');
		$demandtype->description = $request->input('description');
		$demandtype->pointsPerMinute = $request->input('pointsPerMinute');
		$demandtype->pointsPerShift = $request->input('pointsPerShift');
        $demandtype->save();

        return response()->json($demandtype, 201);
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
		$demandtype->description = $request->input('description');
		$demandtype->pointsPerMinute = $request->input('pointsPerMinute');
		$demandtype->pointsPerShift = $request->input('pointsPerShift');
        $demandtype->save();

        return response()->json($demandtype);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $demandtype = Demandtype::findOrFail($id);
        $demandtype->delete();

        return response()->json(null, 204);
    }
}
