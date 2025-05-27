<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancelDetail extends Model
{
    protected $fillable = [
        'cancel_id', 'inventory_id', 'quantity', 'price'
    ];

    public function cancel(): BelongsTo
    {
        return $this->belongsTo(Cancel::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
