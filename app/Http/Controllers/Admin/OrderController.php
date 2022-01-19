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
    public function index(Request $request)
    {
        $admin = $request->user();
        $assigned = $admin->assigned()->get();

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $assigned
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
