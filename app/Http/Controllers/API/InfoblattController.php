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
        // Path to the PDF file in storage
        $filePath = 'public/infoblaetter/' . $year . '/' . $month . '.pdf';
        // Check if the file exists
        if (!Storage::exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }
        // Return the PDF file
        return Response::file(storage_path('app/' . $filePath));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf',
            'month' => 'required|string|max:2',
            'year' => 'required|string|max:4',
        ]);
    
        $year = $request->year; // Or determine the year based on your application logic
        $month = $request->month;
        $file = $request->file('file');
    
        $path = $file->storeAs("infoblaetter/{$year}", "{$month}.pdf", 'public'); // Change 'public' to your desired disk
    
        // Update or create the Infoblatt record
        $infoblatt = Infoblatt::updateOrCreate(
            ['year' => $year],
            ['m'.$month => $path]
        );
    
        return response()->json(['message' => 'File uploaded successfully.', 'path' => $path]);
    }

}
