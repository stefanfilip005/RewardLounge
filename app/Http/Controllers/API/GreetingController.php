<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Greeting;
use Illuminate\Http\Request;

class GreetingController extends Controller
{
    public function show()
    {
        $greeting = Greeting::first();

        if ($greeting) {
            return response()->json($greeting);
        } else {
            return response()->json("", 200);
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
    
        return response()->json($greeting);
    }
}
