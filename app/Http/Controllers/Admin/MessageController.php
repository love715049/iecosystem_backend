<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminMessageCreatedEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function index(Request $request, User $user)
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

    public function create(Request $request, User $user)
    {
        $body = $request->get('body');
        if (!$body) {
            return response()->json([
                'message' => __('normal.required', ['field' => 'body'])
            ], 400);
        }

        $admin = $request->user();
        $order = $this->getOrder($user);
        $message = $order->messages()->create([
            'user_id' => $admin->id,
            'body' => $body
        ]);

        event(new AdminMessageCreatedEvent($message->refresh()));

        return response()->json([
            'message' => __('normal.successful'),
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
