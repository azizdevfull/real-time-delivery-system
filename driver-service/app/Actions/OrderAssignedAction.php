<?php

namespace App\Actions;

use Azizdev\MicroRabbit\Attributes\ConsumeEvent;

#[ConsumeEvent('order.assigned', 'delivery_events', 'driver_queue')]
class OrderAssignedAction
{
    public function rules()
    {
        return [
            'order_id' => 'required',
            'driver_id' => 'required',
        ];
    }
    public function handle(array $payload)
    {
        info('Order assigned', [
            'payload' => $payload
        ]);
    }
}
