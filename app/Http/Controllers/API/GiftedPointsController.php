<?php

namespace App\Http\Controllers\API;

use App\Models\GiftedPoint;
use Illuminate\Http\Request;
use App\Http\Resources\GiftedPointResource;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class GiftedPointsController extends Controller
{
    /**
     * Get logged-in user's gifted points
     */
    public function indexForUser(Request $request)
    {
        $user = $request->user();
        $points = GiftedPoint::where('receiver_remote_id', $user->remote_id)
            ->orderBy('gifted_at', 'desc')
            ->get();

        return GiftedPointResource::collection($points);
    }

    /**
     * Admin: List all gifted points
     */
    public function index()
    {
        $points = GiftedPoint::orderBy('gifted_at', 'desc')
            ->get();

        return GiftedPointResource::collection($points);
    }

    /**
     * Admin: Update gifted points
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'points' => 'required|integer|min:0',
            'gifted_at' => 'required|date',
            'receiver_remote_id' => 'required|integer'
        ]);
    
        if ($validated['points'] === 0) {
            $point = GiftedPoint::where([
                'receiver_remote_id' => $validated['receiver_remote_id'],
                'gifted_at' => $validated['gifted_at']
            ])->first();
    
            if ($point) {
                $point->delete();
            }
            
            return response()->noContent();
        }
        
        // Try to find an existing record first
        $point = GiftedPoint::where([
            'receiver_remote_id' => $validated['receiver_remote_id'],
            'gifted_at' => $validated['gifted_at']
        ])->first();
    
        if ($point) {
            // Explicitly update fields
            $point->points = $validated['points'];
            $point->giver_remote_id = $request->user()->remoteId;
            $point->save();
        } else {
            // Create a new entry if it doesn't exist
            $point = GiftedPoint::create($validated);
        }
    
        return new GiftedPointResource($point);
    }

    /**
     * Admin: Soft delete
     */
    public function destroy($id)
    {
        $point = GiftedPoint::findOrFail($id);
        $point->delete();
        
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}