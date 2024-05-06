<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Greeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GreetingController extends Controller
{
    const CACHE_KEY = 'greeting';
    public function show()
    {
        $greeting = Redis::get(self::CACHE_KEY);
        if ($greeting) {
            return response()->json(json_decode($greeting, true));
        } else {
            $greeting = Greeting::first();
            if ($greeting) {
                Redis::setex(self::CACHE_KEY, 86400, json_encode($greeting->toArray()));
                return response()->json($greeting);
            } else {
                return response()->json("", 200);
            }
        }
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate(['text' => 'string|nullable']);
        $validatedData['text'] = $validatedData['text'] ?? '';
        $greeting = Greeting::first();
        if ($greeting) {
            $greeting->update($validatedData);
        } else {
            $greeting = Greeting::create($validatedData);
        }
        Redis::setex(self::CACHE_KEY, 86400, json_encode($greeting->toArray()));
        return response()->json($greeting);
    }
}
