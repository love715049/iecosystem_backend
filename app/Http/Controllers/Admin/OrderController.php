<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public const ORDER_STATUS = [
        0 => '無',
        1 => '進行中',
        2 => '已結束'
    ];

    public function index(Request $request)
    {
        $admin = $request->user();
        $assigned = $admin->assigned()->get();

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $assigned
        ]);
    }

    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'perPage' => ['integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(Arr::add($validator->getMessageBag()->toArray(), 'success', 'false'));
        }

        $validated = $validator->validated();
        $perPage = Arr::get($validated, 'perPage', 10);

        $data = Order::with('order_type', 'owner', 'user')->orderByDesc('created_at')
            ->paginate($perPage)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user' => Arr::get($item->user, 'name'),
                    'number' => $item->number,
                    'created_at' => $item->created_at->format('Y/m/d'),
                    'order_type_name' => Arr::get($item->order_type, 'name'),
                    'assign' => Arr::get($item->owner, 'name'),
                    'status' => Arr::get(self::ORDER_STATUS, $item->status, self::ORDER_STATUS[0]),
                ];
            });

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $data
        ]);
    }

    public function messages(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'perPage' => ['integer'],
            'sort' => ['string', Rule::in(['asc', 'desc'])],
        ]);

        if ($validator->fails()) {
            return response()->json(Arr::add($validator->getMessageBag()->toArray(), 'success', 'false'));
        }

        $validated = $validator->validated();

        $perPage = Arr::get($validated, 'perPage', 10);
        $sort = Arr::get($validated, 'sort', 'asc');

        $admin = $request->user();
        $messages = $order->messages()->orderBy('created_at', $sort)->paginate(
            $perPage, $columns = ['id', 'user_id', 'body', 'created_at']
        );

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $messages->items()
        ]);
    }

    public function assign(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'order_type_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(Arr::add($validator->getMessageBag()->toArray(), 'success', 'false'));
        }

        $validated = $validator->validated();

        $order->status = 1;
        $order->order_type_id = Arr::get($validated, 'order_type_id');
        $order->assign_id = Arr::get($validated, 'user_id');
        $order->save();

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $order->refresh()
        ]);
    }
}
