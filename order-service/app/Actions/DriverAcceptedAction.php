<?php

namespace App\Actions;

use App\Models\Order;
use Azizdev\MicroRabbit\Attributes\ConsumeEvent;
use Illuminate\Support\Facades\DB;

#[ConsumeEvent('driver.accepted', queue: 'order_queue')]
class DriverAcceptedAction
{
    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'driver_id' => 'required',
        ];
    }

    public function handle(array $payload)
    {
        DB::transaction(function () use ($payload) {

            $order = Order::findOrFail($payload['order_id']);

            $order->driver_id = $payload['driver_id'];

            $order->status = 'delivering';

            $order->save();
        });
    }
}
