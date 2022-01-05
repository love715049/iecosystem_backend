<?php

namespace App\Http\Controllers;

use App\Events\UserMessageCreatedEvent;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function index(Request $request)
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

        $user = $request->user();
        $order = $user->orders()->processed()->first();
        if (!$order) {
            return response()->json([
                'message' => __('normal.no_content')
            ], 204);
        }
        $messages = $order->messages()->orderBy('created_at', $sort)->paginate(
            $perPage, $columns = ['id', 'user_id', 'body', 'created_at']
        );

        return response()->json([
            'message' => __('normal.successful'),
            'data' => $messages->items()
        ]);
    }

    public function create(Request $request)
    {
        $body = $request->get('body');
        if (!$body) {
            return response()->json([
                'message' => __('normal.required', ['field' => 'body'])
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
            'message' => __('normal.successful'),
            'data' => $message->refresh()
        ]);
    }

    public function create_storage(Request $request)
    {
        $body = $request->get('body', []);
        if (!$body) {
            return response()->json([
                'message' => __('normal.required', ['field' => 'body'])
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
            'message' => __('normal.successful'),
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
