<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfoblattResource;
use App\Models\Infoblatt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class InfoblattController extends Controller
{
    public function getInfoblaetter($year)
    {
        $infoblaetter = Infoblatt::where('year', $year)->get();
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
    
        return response()->json(['message' => 'File uploaded successfully.', 'path' => $path]);
    }

}
