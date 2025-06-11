<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallbackRequest;
use App\Models\Callback;

class CallbackController extends Controller
{
    public function store(CallbackRequest $request)
    {
        $callback = Callback::create($request->validated());

        return response()->json([
            'message' => 'Заявка успешно отправлена',
            'data'    => $callback,
        ], 201);
    }
}
