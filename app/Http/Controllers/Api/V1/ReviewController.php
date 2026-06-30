<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReviewStatus;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReviewController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()->with(['user', 'product']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'))
                ->where('status', ReviewStatus::Approved->value);
        } else {
            $query->where('user_id', $request->user()->id);
        }

        $reviews = $query->latest()->paginate((int) $request->input('per_page', 10))->withQueryString();

        return $this->respondOk(ReviewResource::collection($reviews));
    }

    public function productReviews(Product $product): JsonResponse
    {
        Product::query()->published()->findOrFail($product->id);

        $reviews = Review::query()
            ->with(['user', 'product'])
            ->where('product_id', $product->id)
            ->where('status', ReviewStatus::Approved->value)
            ->latest()
            ->paginate(10);

        return $this->respondOk(ReviewResource::collection($reviews));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $verifiedPurchase = false;

        if (! empty($data['order_id'])) {
            $verifiedPurchase = Order::query()
                ->whereKey($data['order_id'])
                ->where('user_id', $request->user()->id)
                ->whereHas('items', fn ($query) => $query->where('product_id', $data['product_id']))
                ->exists();
        }

        if (! $verifiedPurchase) {
            $verifiedPurchase = Order::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('status', ['paid', 'shipped', 'delivered'])
                ->whereHas('items', fn ($query) => $query->where('product_id', $data['product_id']))
                ->exists();
        }

        $review = Review::query()->updateOrCreate([
            'product_id' => $data['product_id'],
            'user_id' => $request->user()->id,
        ], [
            'order_id' => $data['order_id'] ?? null,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'verified_purchase' => $verifiedPurchase,
            'status' => ReviewStatus::Pending->value,
        ]);

        return $this->respondCreated(ReviewResource::make($review));
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorizeReviewOwner($request, $review);

        $data = $request->validate([
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $review->update($data);

        return $this->respondOk(ReviewResource::make($review->refresh()));
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorizeReviewOwner($request, $review);
        $review->delete();

        return $this->respondMessage('Review deleted.');
    }

    private function authorizeReviewOwner(Request $request, Review $review): void
    {
        if ($review->user_id !== $request->user()->id && ! $request->user()->is_admin) {
            throw ValidationException::withMessages([
                'review' => ['You do not have access to this review.'],
            ]);
        }
    }
}
