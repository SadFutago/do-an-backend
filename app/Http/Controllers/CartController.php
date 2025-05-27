<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user_id;
        $cart = Cart::select('id', 'user_id')->with([
            'details:id,cart_id,product_id,quantity',
            'details.product:id,name,price,original_price,quantity,slug',
            'details.product.images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id')->limit(1);
            },
        ])->where('user_id', $userId)->first();
        if (!$cart) {
            return;
        }
        return response()->json($cart);
    }

    public function add(Request $request, $id)
    {
        $userId = $request->user_id;
        $request->validate(['quantity' => 'required|integer|min:1']);
        $product = Product::find($id);
        if (!$product) {
            return;
        }
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        $cartDetail = CartDetail::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if ($cartDetail) {
            $cartDetail->quantity += $request->quantity;
            $cartDetail->save();
        } else {
            $cartDetail = CartDetail::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }
        return response()->json($cartDetail->load([
            'product:id,name,price,original_price,quantity,slug',
            'product.images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id')->limit(1);
            },
        ]), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $userId = $request->user_id;
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) {
            return;
        }
        $cartDetail = CartDetail::where('cart_id', $cart->id)->where('product_id', $id)->first();
        if (!$cartDetail) {
            return;
        }
        $cartDetail->quantity = $request->quantity;
        $cartDetail->save();
        return response()->json(['message' => 'success'], 200);
    }

    public function remove(Request $request, $id)
    {
        $userId = $request->user_id;
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) {
            return;
        }
        $cartDetail = CartDetail::where('cart_id', $cart->id)->where('id', $id)->first();
        if (!$cartDetail) {
            return;
        }
        $cartDetail->delete();
        return response()->json(['message' => 'success'], 204);
    }

    public function clear(Request $request)
    {
        $userId = $request->user_id;
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) {
            return;
        }
        $cart->details()->delete();
        return response()->json(['message' => 'success'], 204);
    }
}