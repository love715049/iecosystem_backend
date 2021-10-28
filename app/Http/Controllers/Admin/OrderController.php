<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $assigned = $admin->assigned()->get();

        return response()->json([
            'message' => 'Successful.',
            'data' => $assigned
        ]);
    }

    public function messages(Request $request, Order $order)
    {
        $perPage = $request->get('perPage', 10);
        $admin = $request->user();
        $messages = $order->messages()->paginate(
            $perPage, $columns = ['id', 'user_id', 'body', 'created_at']
        );

        return response()->json([
            'message' => 'Successful.',
            'data' => $messages->items()
        ]);
    }

    public function assign(Request $request, Order $order)
    {
        $admin = $request->user();
        $order->assign_id = $admin->id;
        $order->save();

        return response()->json([
            'message' => 'Successful.',
            'data' => $order->refresh()
        ]);
    }
}
