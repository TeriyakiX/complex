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

        // Пагинация с лимитом на странице
        $reviews = Review::with('user')->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Все отзывы',
            'data'    => ReviewResource::collection($reviews),
            'links'   => $reviews->links(),
            'meta'    => $reviews->getMeta(),
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

        // Пагинация одобренных отзывов
        $reviews = Review::with('user')
            ->where('status', 'approved')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Одобренные отзывы',
            'data'    => ReviewResource::collection($reviews),
            'links'   => $reviews->links(),
            'meta'    => $reviews->toArray()['meta'],
        ]);
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return response()->json(['message' => 'Отзыв одобрен']);
    }
}
