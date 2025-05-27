<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_number', 'quantity', 'manufacture_date', 'expiry_date',
        'product_id', 'import_id'
    ];

    protected $casts = [
        'manufacture_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'inventory_location')->withPivot('quantity');
    }

    public function exportDetails(): HasMany
    {
        return $this->hasMany(ExportDetail::class);
    }

    public function disposalDetails(): HasMany
    {
        return $this->hasMany(DisposalDetail::class);
    }

    public function checkDetails(): HasMany
    {
        return $this->hasMany(CheckDetail::class);
    }

    public function cancelDetails(): HasMany
    {
        return $this->hasMany(CancelDetail::class);
    }
}
