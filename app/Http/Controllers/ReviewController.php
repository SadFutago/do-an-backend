<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Product;

class ReviewController extends Controller
{
    public function getByProductSlug(string $slug, Request $request) {
        $product = Product::where('slug', $slug)->firstOrFail();

        $reviews = Review::with([
            'user:id,name,avatar_id',
            'user.avatar:id,path',
            'images:id,path,review_id',
        ])->where('product_id', $product->id)
        ->select('id', 'user_id', 'product_id', 'rating', 'comment', 'updated_at')
        ->orderBy('created_at', 'desc');

        return $this->getListResponse($reviews, $request, [], [], 10);
    }
    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);
        $order = Order::where('user_id', $request->user_id)
        ->where('status', Order::STATUS_COMPLETED)
        ->whereHas('details', function($query) use ($request) {
            $query->where('product_id', $request->product_id);
        })
        ->exists();
        if (!$order) {
            return response()->json(['message' => 'You can only review products you have purchased'], 403);
        }
        $review = Review::create($request->all());
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $review);
        }
        return response()->json(['message' => 'success'], 201);
    }
}
