<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderEmailImages
{
    /**
     * Resolve small local product images that can safely be embedded in an email.
     */
    public static function productPaths(Order $order): array
    {
        $disk = Storage::disk('public');
        $paths = [];

        foreach ($order->orderItems as $item) {
            if (!$item->src1) {
                continue;
            }

            $pathInfo = pathinfo($item->src1);
            if (empty($pathInfo['filename']) || empty($pathInfo['extension'])) {
                continue;
            }

            $directory = isset($pathInfo['dirname']) && $pathInfo['dirname'] !== '.'
                ? trim(str_replace('\\', '/', $pathInfo['dirname']), '/').'/'
                : '';
            $thumbnailPath = $directory.$pathInfo['filename'].'_thumb.'.$pathInfo['extension'];

            if ($disk->exists($thumbnailPath)) {
                $paths[$item->getKey()] = $disk->path($thumbnailPath);
            }
        }

        return $paths;
    }
}
