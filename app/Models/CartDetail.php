<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected $appends = ['price', 'subtotal'];

    public function getPriceAttribute()
    {
        return $this->product ? $this->product->price : 0;
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}