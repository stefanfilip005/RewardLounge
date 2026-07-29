<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\Order;
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
        $handledBy = Employee::where('remoteId', $this->order->state_3_user_id)->first();

        return $this->view('emails.orderReadyForTakeaway')
                    ->with([
                        'handledBy' => $handledBy,
                    ])
                    ->subject('Bestellung abholbereit - Rotes Kreuz Hollabrunn - Intern');
    }
}
