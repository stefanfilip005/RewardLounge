<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfoblattResource;
use App\Models\Infoblatt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

class InfoblattController extends Controller
{
    const INFOBLAETTER_CACHE_PREFIX = 'infoblaetter:';

    public function getInfoblaetter($year)
    {
        $cacheKey = self::INFOBLAETTER_CACHE_PREFIX . $year;
        $infoblaetter = Redis::get($cacheKey);
        if (!$infoblaetter) {
            $infoblaetter = Infoblatt::where('year', $year)->get();
            Redis::setex($cacheKey, 86400*7, json_encode($infoblaetter));
        } else {
            $infoblaetter = json_decode($infoblaetter);
        }
        return InfoblattResource::collection($infoblaetter);
    }

    public function getInfoblatt($year, $month)
    {
        $filePath = 'public/infoblaetter/' . $year . '/' . $month . '.pdf';
        if (!Storage::exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        return Response::file(storage_path('app/' . $filePath));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
        ]);
    
        $year = $request->year;
        $month = $request->month;
        $file = $request->file('file');
    
        $path = $file->storeAs("infoblaetter/{$year}", "{$month}.pdf", 'public');
    
        Infoblatt::updateOrCreate(
            ['year' => $year],
            ['m'.$month => $path]
        );

        $cacheKey = self::INFOBLAETTER_CACHE_PREFIX . $year;
        $infoblaetter = Infoblatt::where('year', $year)->get();
        Redis::setex($cacheKey, 86400*7, json_encode($infoblaetter));
    
        return response()->json(['message' => 'File uploaded successfully.', 'path' => $path]);
    }

}
