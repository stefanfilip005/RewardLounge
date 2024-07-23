<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Reward;
use App\Http\Requests\RewardRequest;
use App\Http\Resources\RewardResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use stdClass;

class RewardsController extends Controller
{



    public function getPointStatistic(){
        $pointsPerYear = Shift::select(
            DB::raw('YEAR(start) as year'),
                DB::raw('SUM(COALESCE(overwrittenPoints, points)) as total_points')
            )
            ->whereYear('start', '>=', 2023)
            ->groupBy(DB::raw('YEAR(start)'))
            ->orderBy('year')
            ->get();

        $result = array();
        foreach($pointsPerYear as $point){
            $obj = new stdClass();
            $obj->total_points = $point->total_points;
            $result[$point->year] = $obj;
        }



        $currentYear = now()->year;
        $yearlyPointsSummary = [];

        for ($year = 2023; $year <= $currentYear; $year++) {
            $yearlyPoints = Order::whereYear('created_at', $year)->where('state', '!=', 5)->sum('total_points');

            $yearlyPointsWithArticle = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereYear('orders.created_at', $year)
                ->where('orders.state', '!=', 5)
                ->where('order_items.article_number', '!=', '')
                ->selectRaw('SUM(order_items.points * order_items.quantity) as total_points')
                ->value('total_points');


            $obj = new stdClass();
            $obj->year = $year;

            $obj->usedPoints = $yearlyPoints;
            $obj->usedPointsWithArticle = $yearlyPointsWithArticle ?? 0;
            $yearlyPointsSummary[$year] = $obj;
            if(isset($result[$year])){
                $yearlyPointsSummary[$year]->collectedPoints = $result[$year]->total_points;
            }
        }

        return response()->json($yearlyPointsSummary);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewards = Reward::where('is_active',true)->paginate(500);
        return RewardResource::collection($rewards);
    }

    public function indexAll()
    {
        $rewards = Reward::paginate(500);
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
        $reward->comment_required = $request->input('comment_required', false);
        $reward->article_number = $request->input('article_number','');
        if($reward->article_number == null){
            $reward->article_number = '';
        }


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
		$reward->is_active = $request->input('is_active');
        $reward->comment_required = $request->input('comment_required', false);
        $reward->article_number = $request->input('article_number','');
        if($reward->article_number == null){
            $reward->article_number = '';
        }

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
