<?php

namespace App\Models;

use App\Services\AddressService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use SoftDeletes;

    const PAYMENT_METHOD_QR = 'Mã QR';
    const PAYMENT_METHOD_CARD = 'Thẻ tín dụng / thẻ ghi nợ';
    const PAYMENT_METHOD_EWALLET = 'Ví điện tử';
    const PAYMENT_METHOD_COD = 'Thanh toán khi nhận hàng';
    const PAYMENT_STATUS_PENDING = 0;       // chưa thanh toán
    const PAYMENT_STATUS_PAID = 1;         // đã thanh toán
    const STATUS_PENDING = 0;
    const STATUS_PACKED = 1;               // đóng gói
    const STATUS_SHIPPED = 2;              // vận chuyển
    const STATUS_COMPLETED = 3;            // thành công
    const STATUS_CANCELLED = 4;            // hủy bỏ

    protected $fillable = [
        'user_id',
        'approved_by',
        'voucher_id',
        'status',
        'shipping_fee',
        'total_amount',
        'name',
        'phone',
        'city_id',
        'district_id',
        'ward_id',
        'shipping_address',
        'address_type',
        'payment_method',
        'payment_info',
        'payment_status',
        'shipped_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
    ];
    
    protected $casts = [
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    
    protected $appends = ['product_total'];

    public function getProductTotalAttribute()
    {
        return $this->details->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });
    }

    public function getCity()
    {
        if (!$this->city_id) {
            return null;
        }
        
        return app(AddressService::class)->getProvince($this->city_id);
    }

    public function getDistrict()
    {
        if (!$this->district_id) {
            return null;
        }
        
        return app(AddressService::class)->getDistrict($this->district_id);
    }

    public function getWard()
    {
        if (!$this->ward_id) {
            return null;
        }
        
        return app(AddressService::class)->getWard($this->ward_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}