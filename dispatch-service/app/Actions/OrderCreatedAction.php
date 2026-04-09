<?php

namespace App\Actions;

use Azizdev\MicroRabbit\Attributes\ConsumeEvent;
use Azizdev\MicroRabbit\Facades\MicroRabbit;
use Illuminate\Support\Facades\Log;

#[ConsumeEvent('order.created', 'delivery_events', 'dispatch_queue')]
class OrderCreatedAction
{
    public function rules()
    {
        return [
            'user_id' => 'required',
            'order_id' => 'required',
        ];
    }
    public function handle(array $payload)
    {
        Log::info('Order received in Dispatch', $payload);

        // 1. Driver tanlash (temporary)
        $driverId = rand(1, 10);

        // 2. Event chiqarish
        MicroRabbit::publish('order.assigned', [
            'order_id' => $payload['order_id'],
            'driver_id' => $driverId,
        ]);

        Log::info('Order assigned', [
            'order_id' => $payload['order_id'],
            'driver_id' => $driverId,
        ]);
    }
}
