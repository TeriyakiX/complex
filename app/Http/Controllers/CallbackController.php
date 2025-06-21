<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallbackRequest;
use App\Http\Resources\CallbackResource;
use App\Models\Callback;

class CallbackController extends Controller
{
    public function store(CallbackRequest $request)
    {
        $callback = Callback::create($request->validated());

        return response()->json([
            'message' => 'Заявка успешно отправлена',
            'data'    => new CallbackResource($callback),
        ], 201);
    }
}
