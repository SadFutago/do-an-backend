<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Promotion;

class ProductObserver
{
    public function saving(Product $product): void
    {
        // if ($this->needsPriceRecalculation($product)) {
        //     $product->price = $this->calculateFinalPrice(
        //         $product->original_price,
        //         $product->promotion_id
        //     );
        // }
    }

    private function needsPriceRecalculation(Product $product): bool
    {
        $relevantFields = ['original_price', 'promotion_id'];
        foreach ($relevantFields as $field) {
            if ($product->isDirty($field)) {
                return true;
            }
        }
        return false;
    }

    private function calculateFinalPrice(int $originalPrice, ?int $promotionId): int
    {
        if (!$promotionId) {
            return $originalPrice;
        }
        $promotion = Promotion::find($promotionId);
        if ($promotion && $promotion->status == Promotion::STATUS_ACTIVE) {
            $discountAmount = (int)round($originalPrice * ($promotion->discount_percent / 100));
            if ($promotion->max_discount_amount && $discountAmount > $promotion->max_discount_amount) {
                $discountAmount = $promotion->max_discount_amount;
            }
            return max(0, $originalPrice - $discountAmount);
        }
        return $originalPrice;
    }
}
