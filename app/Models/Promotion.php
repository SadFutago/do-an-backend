<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;
    
    const STATUS_UPCOMING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 2;

    protected $fillable = [
        'name',
        'discount_percent',
        'max_discount_amount',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'status' => 'integer',
        'discount_percent' => 'integer',
        'max_discount_amount' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
