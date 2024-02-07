<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Reward;
use App\Http\Requests\RewardRequest;
use App\Http\Resources\RewardResource;
use Illuminate\Support\Facades\Storage;

class RewardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewards = Reward::paginate(15);
        return RewardResource::collection($rewards);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  RewardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RewardRequest $request)
    {
        $reward = new Reward;
		$reward->name = $request->input('name');
		$reward->slogan = $request->input('slogan');
		$reward->description = $request->input('description');

        if ($request->has('src1')) {
            $base64Image = $request->input('src1');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $type = strtolower($type[1]); 
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
    
                // Original image
                $fileName = 'reward_images/' . uniqid('', true);
                $filePath = $fileName . '.' . $type;
                Storage::disk('public')->put($filePath, $imageData);
                $reward->src1 = $filePath;
    
                // Create thumbnail
                $thumbFileName = $fileName . '_thumb.' . $type;
                $this->createThumbnail($imageData, $thumbFileName, $type, 300, 300);
            }
        }
    
        $reward->points = $request->input('points');
        $reward->euro = $request->input('price');
        $reward->valid_from = "2023-01-01"; // Consider dynamic date
        $reward->valid_to = null;
        $reward->save();

        return response()->json($reward, 201);
    }

    
    protected function createThumbnail($imageData, $thumbFileName, $type, $maxWidth, $maxHeight)
    {
        $image = imagecreatefromstring($imageData);
        if (!$image) return;

        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $thumbWidth = intval($width * $ratio);
        $thumbHeight = intval($height * $ratio);

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresized($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        $savePath = storage_path('app/public/') . $thumbFileName;
        switch ($type) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($thumb, $savePath);
                break;
            case 'png':
                imagepng($thumb, $savePath);
                break;
            case 'gif':
                imagegif($thumb, $savePath);
                break;
        }
        imagedestroy($image);
        imagedestroy($thumb);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reward = Reward::findOrFail($id);
        return new RewardResource($reward);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  RewardRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RewardRequest $request, $id)
    {
        $reward = Reward::findOrFail($id);
		$reward->name = $request->input('name');
		$reward->slogan = $request->input('slogan');
		$reward->description = $request->input('description');

        if ($request->has('src1')) {
            $base64Image = $request->input('src1');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $type = strtolower($type[1]); 
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
    
                // Original image
                $fileName = 'reward_images/' . uniqid('', true);
                $filePath = $fileName . '.' . $type;
                Storage::disk('public')->put($filePath, $imageData);
                $reward->src1 = $filePath;
    
                // Create thumbnail
                $thumbFileName = $fileName . '_thumb.' . $type;
                $this->createThumbnail($imageData, $thumbFileName, $type, 300, 300);
            }
        }

		$reward->points = $request->input('points');
		$reward->euro = $request->input('euro');
        $reward->save();

        return response()->json($reward);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);
        $reward->delete();

        return response()->json(null, 204);
    }
}
