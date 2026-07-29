<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\EmployeePictureStorage;
use App\Support\OrderEmailImages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedForTeam extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $this->order->loadMissing(['employee', 'orderItems']);

        return $this->view('emails.orderPlacedForTeam')
                    ->with([
                        'employeePicturePath' => app(EmployeePictureStorage::class)->absolutePath($this->order->remoteId),
                        'productImagePaths' => OrderEmailImages::productPaths($this->order),
                    ])
                    ->subject('[intern.rkhl.at] - Neue Bestellung eingegangen!');
    }
}
