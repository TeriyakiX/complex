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

        return ReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = Review::create([
            'user_id' => Auth::id(),
            'text'    => $request->text,
        ]);

        return response()->json([
            'message' => 'Отзыв отправлен на модерацию',
            'review'  => new ReviewResource($review),
        ], 201);
    }

    public function index()
    {
        $reviews = Review::with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);
        return response()->json(['message' => 'Отзыв одобрен']);
    }
}
