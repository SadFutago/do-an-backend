<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportDetail extends Model
{
    protected $fillable = [
        'export_id', 'inventory_id', 'quantity', 'actual_quantity', 'price'
    ];

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}