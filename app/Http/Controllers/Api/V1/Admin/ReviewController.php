<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        $reviews = Review::query()
            ->with(['user', 'product'])
            ->latest()
            ->paginate((int) $request->input('per_page', 15));

        return $this->respondOk($reviews);
    }

    public function approve(Request $request, Review $review): JsonResponse
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        $review->update(['status' => 'approved']);

        return $this->respondOk($review->refresh());
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        $review->delete();

        return $this->respondMessage('Review deleted.', 204);
    }
}
