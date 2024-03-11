<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\OrderResource;
use App\Jobs\ProcessPoints;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function getSelfOrders(Request $request)
    {
        $user = $request->user();
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::where('remoteId', $user->remoteId)->with('orderItems')->orderBy('created_at', 'desc')->paginate(250);

        return OrderResource::collection($orders);
    }


    public function getOrders(Request $request)
    {
        // Use eager loading with 'with' to reduce SQL queries
        $orders = Order::with('orderItems')->orderBy('created_at', 'asc')->paginate(5000);

        return OrderResource::collection($orders);
    }

    
    public function employeesFromOrders(Request $request){
        $orders = Order::get();

        $employeeIds = [];
        foreach ($orders as $order) {
            $employeeIds[$order->remoteId] = true;

            $employeeIds[$order->remoteId] = true;
            if($order->state_1_user_id != null){
                $employeeIds[$order->state_1_user_id] = true;
            }
            if($order->state_2_user_id != null){
                $employeeIds[$order->state_2_user_id] = true;
            }
            if($order->state_3_user_id != null){
                $employeeIds[$order->state_3_user_id] = true;
            }
            if($order->state_4_user_id != null){
                $employeeIds[$order->state_4_user_id] = true;
            }
            if($order->state_5_user_id != null){
                $employeeIds[$order->state_5_user_id] = true;
            }
        }
        $employeeIds = array_keys($employeeIds);

        $employees = Employee::whereIn('remoteId', $employeeIds)->get();
        return EmployeeResource::collection($employees);
    }

    
    public function changeOrderState(Request $request, $id)
    {
        $user = $request->user();
        $newState = $request->input('state');

        $order = Order::findOrFail($id);
        if (!in_array($newState, [0, 1, 2, 3, 4, 5])) {
            return response()->json(['message' => 'Invalid state.'], 400);
        }

        if ($newState == 0) {
            $order->update([
                'state' => $newState,
                'state_1_datetime' => null, 'state_1_user_id' => null,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 1) {
            $order->update([
                'state' => $newState,
                'state_1_datetime' => now(), 'state_1_user_id' => $user->remoteId,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 2) {
            $order->update([
                'state' => $newState,
                'state_2_datetime' => now(), 'state_2_user_id' => $user->remoteId,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 3) {
            $order->update([
                'state' => $newState,
                'state_3_datetime' => now(), 'state_3_user_id' => $user->remoteId,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 4) {
            $order->update([
                'state' => $newState,
                'state_4_datetime' => now(), 'state_4_user_id' => $user->remoteId,
                'state_5_datetime' => null, 'state_5_user_id' => null,
            ]);
        } else if ($newState == 5) {
            $order->update([
                'state' => $newState,
                'state_1_datetime' => null, 'state_1_user_id' => null,
                'state_2_datetime' => null, 'state_2_user_id' => null,
                'state_3_datetime' => null, 'state_3_user_id' => null,
                'state_4_datetime' => null, 'state_4_user_id' => null,
                'state_5_datetime' => now(), 'state_5_user_id' => $user->remoteId,
            ]);   
        }         
        $employee = Employee::where('remoteId',$order->remoteId)->first();
        if ($employee) {
            ProcessPoints::dispatch($employee);
        }

        return response()->json(['message' => 'Order state updated successfully.'], 200);
    }


    public function updateOrderNote(Request $request, $orderId)
    {    
        $order = Order::where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        $note = $request->input('note', '');
        $order->note = $note;
        $order->save();
    
        return response()->json(['message' => 'Order note updated successfully']);
    }


}
