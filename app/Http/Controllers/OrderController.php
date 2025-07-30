<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\WarehouseProduct;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $perPage = (int) request()->query('per_page', 15);
        $orders = Order::with(['product', 'warehouseProduct'])->latest()->paginate($perPage);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = Order::create($request->validated());

        return response()->json([
            'message' => 'Заказ успешно создан',
            'data' => new OrderResource($order),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['product', 'warehouseProduct']);
        return new OrderResource($order);
    }

    public function updateStatus(Order $order, string $status)
    {
        $order->update(['status' => $status]);

        return new OrderResource($order);
    }
}
