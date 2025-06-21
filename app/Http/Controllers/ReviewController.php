<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function all()
    {
        $this->authorize('is-admin');

        $reviews = Review::with('user')->latest()->get();

        return response()->json([
            'message' => 'Все отзывы',
            'data'    => ReviewResource::collection($reviews),
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

    public function index()
    {
        $reviews = Review::with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Одобренные отзывы',
            'data'    => ReviewResource::collection($reviews),
        ]);
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return response()->json(['message' => 'Отзыв одобрен']);
    }
}
