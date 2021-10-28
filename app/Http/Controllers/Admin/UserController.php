<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $users = User::paginate(
            $perPage, $columns = ['id', 'name', 'email']
        );

        return response()->json([
            'message' => 'Successful.',
            'data' => $users->items()
        ]);
    }
}
