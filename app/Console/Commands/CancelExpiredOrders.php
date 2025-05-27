<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Tự động hủy các đơn hàng quá 30 ngày mà chưa được giao';

    public function handle()
    {
        $startTime = now()->toDateTimeString();
        \Log::info("Starting orders:cancel-expired at $startTime");

        try {
            DB::beginTransaction();

            $expirationDate = Carbon::now()->subDays(30);
            $orders = Order::where('created_at', '<', $expirationDate)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->get();

            \Log::info("Orders found: {$orders->count()}", [
                'expiration_date' => $expirationDate->toDateTimeString(),
                'orders' => $orders->toJson()
            ]);

            $count = 0;
            if ($orders->isEmpty()) {
                \Log::info("No orders to cancel at $startTime");
                $this->info("Không có đơn hàng nào để hủy tại $startTime");
            } else {
                foreach ($orders as $order) {
                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                        'cancel_reason' => 'Hủy do quá hạn 30 ngày',
                        'cancelled_at' => now(),
                    ]);
                    $count++;
                }
                \Log::info("Successfully canceled $count orders at $startTime");
                $this->info("Đã hủy thành công $count đơn hàng quá hạn tại $startTime");
            }

            DB::commit();
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = "Error in orders:cancel-expired at $startTime: " . $e->getMessage();
            \Log::error($errorMessage);
            $this->error($errorMessage);
            return 1;
        }
    }
}