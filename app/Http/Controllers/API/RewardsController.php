<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Reward;
use App\Http\Requests\RewardRequest;
use App\Http\Resources\RewardResource;

class RewardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewards = Reward::paginate(15);
        return RewardResource::collection($rewards);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  RewardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RewardRequest $request)
    {
        $reward = new Reward;
		$reward->name = $request->input('name');
		$reward->description = $request->input('description');
		$reward->description2 = $request->input('description2');
		$reward->src2 = $request->input('src2');
		$reward->src3 = $request->input('src3');
		$reward->price = $request->input('price');
		$reward->unsignedinteger = $request->input('unsignedinteger');
		$reward->valid_from = $request->input('valid_from');
		$reward->valid_to = $request->input('valid_to');
        $reward->save();

        return response()->json($reward, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reward = Reward::findOrFail($id);
        return new RewardResource($reward);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  RewardRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RewardRequest $request, $id)
    {
        $reward = Reward::findOrFail($id);
		$reward->name = $request->input('name');
		$reward->description = $request->input('description');
		$reward->description2 = $request->input('description2');
		$reward->src2 = $request->input('src2');
		$reward->src3 = $request->input('src3');
		$reward->price = $request->input('price');
		$reward->unsignedinteger = $request->input('unsignedinteger');
		$reward->valid_from = $request->input('valid_from');
		$reward->valid_to = $request->input('valid_to');
        $reward->save();

        return response()->json($reward);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);
        $reward->delete();

        return response()->json(null, 204);
    }
}
