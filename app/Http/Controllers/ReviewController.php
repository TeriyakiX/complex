<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function all(Request $request)
    {
        $this->authorize('is-admin');

        $perPage = $request->get('per_page', 15);

        $reviews = Review::with('user')->latest()->paginate($perPage);

        $resourceData = ReviewResource::collection($reviews)->response()->getData(true);

        return response()->json([
            'message' => 'Все отзывы',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = Review::create([
            'user_id' => Auth::id(),
            'text'    => $request->text,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'message' => 'Отзыв отправлен на модерацию',
            'data'    => new ReviewResource($review),
        ], 201);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $reviews = Review::with('user')
            ->where('status', 'approved')
            ->latest()
            ->paginate($perPage);

        $resourceData = ReviewResource::collection($reviews)->response()->getData(true);

        return response()->json([
            'message' => 'Одобренные отзывы',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function changeStatus(Review $review, $status)
    {
        if ($status === 'approve') {
            $review->update(['status' => 'approved']);
            return response()->json(['message' => 'Отзыв одобрен']);
        } elseif ($status === 'reject') {
            $review->delete();
            return response()->json(['message' => 'Отзыв отклонен и удален']);
        }

        return response()->json(['message' => 'Неверный статус'], 400);
    }
}
