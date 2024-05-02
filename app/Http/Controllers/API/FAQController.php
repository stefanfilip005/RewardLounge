<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FAQResource;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::orderBy('sort_order', 'asc')->get();
        return FAQResource::collection($faqs);
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        $faq = FAQ::updateOrCreate(['id' => $id], $request->only(['question', 'answer','sort_order']));
        return new FAQResource($faq);
    }

    public function destroy($id)
    {
        $faq = FAQ::findOrFail($id);
        $faq->delete();
        return response()->json(null, 204);
    }
}
