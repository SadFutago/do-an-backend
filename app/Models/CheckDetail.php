<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckDetail extends Model
{
    protected $fillable = [
        'check_id', 'inventory_id', 'quality', 'quantity', 'actual_quantity', 'price'
    ];

    public function check(): BelongsTo
    {
        return $this->belongsTo(Check::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
