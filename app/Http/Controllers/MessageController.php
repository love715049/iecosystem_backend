<?php

namespace App\Http\Controllers;

use App\Events\UserMessageCreatedEvent;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $user = $request->user();
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

    public function create(Request $request)
    {
        $body = $request->get('body');
        if (!$body) {
            return response()->json([
                'message' => 'body is required.'
            ], 400);
        }

        $user = $request->user();
        $order = $this->getOrder($user);
        $message = $order->messages()->create([
            'user_id' => $user->id,
            'body' => $body
        ]);

        event(new UserMessageCreatedEvent($message->refresh()));

        return response()->json([
            'message' => 'Successful.',
            'data' => $message->refresh()
        ]);
    }

    public function create_storage(Request $request)
    {
        $body = $request->get('body', []);
        if (!$body) {
            return response()->json([
                'message' => 'body is required.'
            ], 400);
        }

        $user = $request->user();
        $order = $this->getOrder($user);
        foreach ($body as $message) {
            $messages[] = $order->messages()->create([
                'user_id' => $user->id,
                'body' => $message
            ])->refresh();
        }

        return response()->json([
            'message' => 'Successful.',
            'data' => $messages
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

    private function get_order_number()
    {
        $order_id = optional(Order::latest()->first())->id + 1;
        $date = now()->format('Ymd');
        return '#' . $date . str_pad($order_id, 5, "0", STR_PAD_LEFT);
    }
}
