<?php

namespace App\Actions;

use Azizdev\MicroRabbit\Attributes\ConsumeEvent;
use Illuminate\Support\Facades\Log;

#[ConsumeEvent('order.created', 'delivery_events', 'dispatch_queue')]
class OrderCreatedAction
{
    public function rules()
    {
        return [
            'user_id' => 'required',
            'driver_id' => 'required',
            'status' => 'required',
        ];
    }
    public function handle(array $payload)
    {
        Log::info('Order created', [
            'payload' => $payload
        ]);
        // throw new \Exception('Order created error');
    }
}
