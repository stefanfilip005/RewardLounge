<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\OrderEmailImages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedForCustomer extends Mailable
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

        return $this->view('emails.orderPlacedForCustomer')
                    ->with([
                        'productImagePaths' => OrderEmailImages::productPaths($this->order),
                    ])
                    ->subject('Bestellbestätigung - Rotes Kreuz Hollabrunn - Intern');
    }
}
