<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function close(Request $request, Order $order)
    {
        $user = $request->user();

        $order->status = 2;
        $order->save();

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $order->refresh()
        ]);
    }
}
