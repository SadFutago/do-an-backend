<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Product;
use App\Models\Voucher;
use Faker\Factory as Faker;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $userIds = User::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $voucherIds = Voucher::pluck('id')->toArray();
        $adminUserIds = User::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })->pluck('id')->toArray();

        for ($i = 0; $i < 30; $i++) {
            $userId = $faker->randomElement($userIds);
            $shippingFee = $faker->randomElement([15000, 20000, 25000, 30000]);
            $paymentMethod = $faker->randomElement(['Mã QR', 'Thẻ tín dụng / thẻ ghi nợ', 'Ví điện tử', 'Thanh toán khi nhận hàng']);
            $status = $faker->randomElement([0, 1, 2, 3, 4]); // 0: chờ xác nhận, 1: đã xác nhận, 2: đang giao, 3: hoàn thành, 4: đã hủy

            $createdAt = $faker->dateTimeBetween('-3 months', 'now');
            $shippedAt = null;
            $completedAt = null;
            $cancelledAt = null;
            $cancelReason = null;
            $approvedBy = null;
            
            if ($status >= 1) {
                $approvedBy = !empty($adminUserIds) ? $faker->randomElement($adminUserIds) : null;
            }
            
            if ($status >= 2) {
                $shippedAt = Carbon::instance($createdAt)->addHours($faker->numberBetween(1, 48));
            }
            
            if ($status == 3) {
                $completedAt = Carbon::instance($shippedAt ?? $createdAt)->addDays($faker->numberBetween(1, 7));
            }
            
            if ($status == 4) {
                $cancelledAt = Carbon::instance($createdAt)->addHours($faker->numberBetween(1, 24));
                $cancelReason = $faker->randomElement([
                    'Khách hàng hủy đơn',
                    'Hết hàng',
                    'Địa chỉ giao hàng không chính xác',
                    'Khách hàng thay đổi ý định',
                    'Sản phẩm lỗi'
                ]);
            }

            $order = Order::create([
                'user_id' => $userId,
                'approved_by' => $approvedBy,
                'voucher_id' => $faker->boolean(30) ? $faker->randomElement($voucherIds) : null,
                'status' => $status,
                'shipping_fee' => $shippingFee,
                'total_amount' => 0,
                'name' => $faker->name,
                'phone' => '0' . $faker->numberBetween(300000000, 999999999),
                'shipping_address' => $faker->address,
                'address_type' => $faker->randomElement(['Nhà riêng', 'Văn phòng']),
                'payment_method' => $paymentMethod,
                'payment_info' => $paymentMethod === 'Mã QR' ? 'QR_' . $faker->numberBetween(100000, 999999) : null,
                'payment_status' => $status >= 3 ? 1 : ($status == 4 ? 0 : $faker->randomElement([0, 1])),
                'shipped_at' => $shippedAt,
                'completed_at' => $completedAt,
                'cancelled_at' => $cancelledAt,
                'cancel_reason' => $cancelReason,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $numberOfProducts = $faker->numberBetween(2, 5);
            $selectedProducts = $faker->randomElements($productIds, $numberOfProducts);
            $totalAmount = 0;

            foreach ($selectedProducts as $productId) {
                $quantity = $faker->numberBetween(1, 3);
                $price = $faker->numberBetween(50000, 500000);
                $totalAmount += $quantity * $price;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $order->update(['total_amount' => $totalAmount + $shippingFee]);
        }
    }
} 