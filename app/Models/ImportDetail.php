<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportDetail extends Model
{
    protected $fillable = [
        'import_id', 'product_id', 'quantity', 'actual_quantity', 'price'
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
