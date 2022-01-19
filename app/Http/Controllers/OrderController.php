<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderType;
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

    public function types(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => __('normal.successful'),
            'data' => OrderType::select(['id', 'name'])->get()
        ]);
    }
}
