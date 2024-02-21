<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function getSelfOrders(Request $request)
    {
        $user = $request->user();
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::where('remoteId', $user->remoteId)->with('orderItems')->orderBy('created_at', 'desc')->paginate(25);

        return OrderResource::collection($orders);
    }


    public function getOrders(Request $request)
    {
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::with('orderItems')->orderBy('created_at', 'asc')->paginate(25);

        return OrderResource::collection($orders);
    }

}
