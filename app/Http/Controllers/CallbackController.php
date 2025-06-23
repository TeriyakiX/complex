<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallbackRequest;
use App\Http\Resources\CallbackResource;
use App\Mail\CallbackMail;
use App\Models\Callback;
use App\Services\CallbackStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CallbackController extends Controller
{

    protected $statusService;

    public function __construct(CallbackStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $status = $request->query('status');

        $callbacks = Callback::when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->paginate($perPage);

        $resourceData = CallbackResource::collection($callbacks)->response()->getData(true);

        return response()->json([
            'message' => 'Список заявок',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function show($id)
    {
        $callback = Callback::findOrFail($id);

        return response()->json([
            'message' => 'Данные заявки',
            'data'    => new CallbackResource($callback),
        ]);
    }

    public function store(CallbackRequest $request)
    {
        $callback = Callback::create($request->validated());

        // Отправка email после создания заявки
        Mail::to(['info@ekbcomplex.ru', 'marketing@ekbcomplex.ru'])->send(new CallbackMail($callback));

        return response()->json([
            'message' => 'Заявка успешно отправлена',
            'data'    => new CallbackResource($callback),
        ], 201);
    }

    public function destroy($id)
    {
        $callback = Callback::findOrFail($id);

        $callback->delete();

        return response()->json([
            'message' => 'Заявка успешно удалена',
        ]);
    }

    public function updateStatus($id, $status)
    {
        $callback = Callback::findOrFail($id);

        $result = $this->statusService->updateStatus($callback, $status);

        if ($result['status'] === 400) {
            return response()->json(['message' => $result['message']], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => new CallbackResource($result['data'] ?? $callback),
        ]);
    }

}
