<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, User $user)
    {
        $perPage = $request->get('perPage', 10);
        $admin = $request->user();
        $order = $user->orders()->processed()->first();
        if (!$order) {
            return response()->json([
                'message' => 'No Content.'
            ], 204);
        }
        $messages = $order->messages()->paginate(
            $perPage, $columns = ['id', 'user_id', 'body', 'created_at']
        );

        return response()->json([
            'message' => 'Successful.',
            'data' => $messages->items()
        ]);
    }

    public function create(Request $request, User $user)
    {
        $body = $request->get('body');
        if (!$body) {
            return response()->json([
                'message' => 'body is required.'
            ], 400);
        }

        $admin = $request->user();
        $order = $this->getOrder($user);
        $message = $order->messages()->create([
            'user_id' => $admin->id,
            'body' => $body
        ]);

        return response()->json([
            'message' => 'Successful.',
            'data' => $message->refresh()
        ]);
    }

    private function getOrder($user)
    {
        $order = $user->orders()->processed()->first();
        if (!$order) {
            $order = $user->orders()->create([
                'number' => $this->get_order_number()
            ]);
        }
        return $order;
    }
}
