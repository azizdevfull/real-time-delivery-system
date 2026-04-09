<?php

namespace App\Actions;

use App\Models\Driver;
use Azizdev\MicroRabbit\Attributes\ConsumeEvent;
use Azizdev\MicroRabbit\Exceptions\DoNotRetryException;
use Azizdev\MicroRabbit\Facades\MicroRabbit;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        DB::transaction(function () use ($payload) {

            $driver = Driver::where('id', $payload['driver_id'])->firstOrFail();

            if ($driver->status == Driver::STATUS_BUSY) {
                throw new DoNotRetryException('Driver already busy');
            }

            $driver->status = Driver::STATUS_BUSY;
            $driver->save();

            MicroRabbit::publish('driver.accepted', [
                'order_id' => $payload['order_id'],
                'driver_id' => $payload['driver_id'],
            ]);
        });
    }
}
