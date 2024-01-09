<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Multiplication;
use Illuminate\Http\Request;

class MultiplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewards = Multiplication::paginate(15);
        return RewardResource::collection($rewards);
    }
}
