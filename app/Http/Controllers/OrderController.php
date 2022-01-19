<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderController extends Controller
{
    public const ORDER_STATUS = [
        0 => '無',
        1 => '進行中',
        2 => '已結束'
    ];

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $data = $user->orders()->with('order_type', 'owner')->orderByDesc('created_at')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'number' => $item->number,
                'created_at' => $item->created_at->format('Y/m/d'),
                'order_type_name' => $item->order_type['name'],
                'assign_id' => $item->owner['name'],
                'status' => Arr::get(self::ORDER_STATUS, $item->status, self::ORDER_STATUS[0]),
            ];
        });

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $data
        ]);
    }

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
