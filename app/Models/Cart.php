<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->details->sum(function ($detail) {
            if ($detail->product->quantity > 0)
                return $detail->subtotal;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(CartDetail::class);
    }
}