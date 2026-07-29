<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\Order;
use App\Support\OrderEmailImages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderReadyForTakeaway extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $employee;

    public function __construct(Order $order, Employee $employee)
    {
        $this->order = $order;
        $this->employee = $employee;
    }

    public function build()
    {
        $this->order->loadMissing(['employee', 'orderItems']);
        $handledBy = $this->order->state_3_user_id
            ? Employee::where('remoteId', $this->order->state_3_user_id)->first()
            : null;

        return $this->view('emails.orderReadyForTakeaway')
                    ->with([
                        'handledBy' => $handledBy,
                        'productImagePaths' => OrderEmailImages::productPaths($this->order),
                    ])
                    ->subject('Bestellung abholbereit - Rotes Kreuz Hollabrunn - Intern');
    }
}
