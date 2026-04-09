<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Azizdev\MicroRabbit\Facades\MicroRabbit;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required',
        ]);
        $data['status'] = 'pending';
        $order = Order::create($data);
        MicroRabbit::publish(
            'order.created',
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]
        );

        return response()->json($order);
    }
}
