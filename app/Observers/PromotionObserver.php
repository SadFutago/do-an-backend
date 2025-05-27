<?php

namespace App\Observers;

use App\Models\Promotion;

class PromotionObserver
{
    public function updated(Promotion $promotion): void
    {
        // if ($this->shouldUpdateRelatedProducts($promotion)) {
        //     $this->updateRelatedProducts($promotion);
        // }
    }

    private function shouldUpdateRelatedProducts(Promotion $promotion): bool
    {
        $relevantFields = ['discount_percent', 'max_discount_amount', 'status'];
        foreach ($relevantFields as $field) {
            if ($promotion->isDirty($field)) {
                return true;
            }
        }
        return false;
    }

    private function updateRelatedProducts(Promotion $promotion) {
        $products = $promotion->products;
        foreach ($products as $product) {
            $product->touch();
        }
    }
}
