<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'approved_by',
        'code',
        'value',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function isExhausted()
    {
        return $this->used_count >= $this->usage_limit;
    }

    public function isValid()
    {
        $now = now();
        return $this->start_date <= $now && $this->end_date >= $now && $this->approved_by !== null;
    }

    public function isUsedByUser($userId) 
    {
        return VoucherUsage::where('user_id', $userId)->where('voucher_id', $this->id)->exists();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
