<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Mail\OrderPlacedForCustomer;
use App\Mail\OrderPlacedForTeam;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{

    public function getCartCount(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->first();
        if (!$employee) {
            return response()->json(['count' => 0]);
        }
        $cart = Cart::where('remoteId', $employee->remoteId)->first();
        $count = $cart ? $cart->items->sum('quantity') : 0;
        return response()->json(['count' => $count]);
    }

    public function getCartContents(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items')->where('remoteId', $user->remoteId)->first();
        if (!$cart) {
            return response()->json(['message' => 'No cart found'], 404);
        }
        return new CartResource($cart);
    }


    public function addItem(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->firstOrFail();
        $cart = Cart::firstOrCreate(['remoteId' => $employee->remoteId]);

        $reward_id = $request->input('reward_id');
        $quantity = $request->input('quantity', 1); 
        $note = $request->input('note', ''); 

        $cartItem = CartItem::where([
            ['cart_id', '=', $cart->id],
            ['reward_id', '=', $reward_id],
        ])->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
        } else {
            $cartItem = new CartItem([
                'cart_id' => $cart->id,
                'reward_id' => $reward_id,
                'quantity' => $quantity,
                'note' => $note
            ]);
        }

        if($cartItem->quantity <= 20){
            $cartItem->save();
        }

        return response()->json(['message' => 'Item added to cart successfully', 'cartItem' => $cartItem]);
    }


    public function updateItemQuantity(Request $request, $itemId)
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->firstOrFail();
    
        $cart = Cart::where('remoteId', $employee->remoteId)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
    
        $item = CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first();
        if ($item) {
            $item->quantity = $request->quantity;
            $item->save();
            return response()->json(['message' => 'Item quantity updated successfully']);
        } else {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }
    }
    public function updateItemNote(Request $request, $itemId)
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->firstOrFail();
    
        $cart = Cart::where('remoteId', $employee->remoteId)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
    
        $item = CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first();
        if ($item) {
            $note = $request->input('note', '');
            $item->note = $note;
            $item->save();
            return response()->json(['message' => 'Item note updated successfully']);
        } else {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }
    }

    public function deleteItem(Request $request, $itemId)
    {
        $cart = Cart::where('remoteId', $request->user()->remoteId)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $item = CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first();
        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Item deleted successfully']);
        } else {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }
    }


    public function checkout(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('remoteId', $user->remoteId)->firstOrFail();
    
        $cart = Cart::where('remoteId', $employee->remoteId)->first();
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }


        foreach ($cart->items as $cartItem) {
            if($cartItem->quantity <= 0){
                return response()->json(['message' => 'Quantity must be greater than 0'], 400);
            }
        }

    
        DB::beginTransaction();
        try {
            $totalPoints = 0; // Initialize total points

            $order = new Order(['remoteId' => $employee->remoteId,'created_at_datetime' => now()]);
            $order->save();
    
            foreach ($cart->items as $cartItem) {
                $reward = $cartItem->reward; 
                
                $totalPoints += $reward->points * $cartItem->quantity;
                
                $order->orderItems()->create([
                    'reward_id' => $cartItem->reward_id,
                    'quantity' => $cartItem->quantity,
                    'note' => $cartItem->note,
                    'name' => $reward->name,
                    'slogan' => $reward->slogan,
                    'description' => $reward->description,
                    'src1' => $reward->src1,
                    'points' => $reward->points,
                    'euro' => $reward->euro,
                    'article_number' => $reward->article_number,
                ]);
            }

            $order->total_points = $totalPoints;
            $order->save();
    
            $cart->items()->delete();
            $cart->delete();

            $employee->points = $employee->points - $totalPoints;
            $employee->save();
    
            DB::commit();
            Mail::to($employee->email)->send(new OrderPlacedForCustomer($order));

            $employees = Employee::where('isModerator', true)->orWhere('isAdministrator', true)->orWhere('isDeveloper', true)->get(['email']);
            $teamEmails = array();
            foreach ($employees as $employeeMail) {
                $teamEmails[] = $employeeMail->email;
            }
            foreach ($teamEmails as $teamEmail) {
                Mail::to($teamEmail)->send(new OrderPlacedForTeam($order));
            }

            return response()->json(['message' => 'Order placed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing order', 'error' => $e->getMessage()], 500);
        }
    }


}
