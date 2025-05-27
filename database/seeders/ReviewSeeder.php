<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Faker\Factory as Faker;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        $completedOrders = Order::where('status', 3)->get();

        $positiveComments = [
            'Sản phẩm chất lượng tuyệt vời! Đúng như mô tả, đóng gói cẩn thận.',
            'Mình rất hài lòng với sản phẩm này. Giao hàng nhanh, chất lượng tốt.',
            'Chất lượng vượt mong đợi! Sẽ mua lại lần sau.',
            'Sản phẩm tốt, giá hợp lý. Đóng gói rất chắc chắn.',
            'Dịch vụ khách hàng tuyệt vời, sản phẩm chính hãng.',
            'Đúng như hình ảnh, chất lượng cao. Rất recommend!',
            'Mua về dùng rất ổn, phù hợp với xe của mình.',
            'Shop giao hàng nhanh, sản phẩm chất lượng tốt.',
            'Phụ tùng chính hãng, bền đẹp. Sẽ tiếp tục ủng hộ shop.',
            'Sản phẩm đúng mô tả, lắp vào xe rất vừa vặn.',
            'Chất lượng premium, đáng đồng tiền bát gạo.',
            'Mình đã dùng 3 tháng rồi, vẫn tốt như mới.',
            'Sản phẩm tốt, shop hỗ trợ nhiệt tình.',
            'Chất liệu cao cấp, gia công tỉ mỉ.',
            'Giao hàng siêu nhanh, đóng gói cẩn thận. 5 sao!',
            'Phụ tùng chất lượng, lắp đặt dễ dàng.',
            'Sản phẩm tốt, giá cả phải chăng. Sẽ mua thêm.',
            'Chất lượng quốc tế, rất hài lòng với lần mua này.',
            'Đóng gói siêu kỹ, sản phẩm nguyên vẹn 100%.',
            'Phụ tùng tốt, nâng cấp xe hiệu quả.',
        ];

        $goodComments = [
            'Sản phẩm tốt, đúng như mô tả. Sẽ ủng hộ shop tiếp.',
            'Chất lượng ổn, giao hàng đúng hẹn.',
            'Sản phẩm tạm được, phù hợp với giá tiền.',
            'Dùng tạm ổn, chưa thấy vấn đề gì.',
            'Sản phẩm bình thường, không có gì đặc biệt.',
            'Chất lượng tương đối, giá hợp lý.',
            'Dùng được, không quá tệ cũng không quá tốt.',
            'Sản phẩm OK, đóng gói cẩn thận.',
            'Tạm ổn với mức giá này.',
            'Phụ tùng dùng được, chưa test lâu dài.',
        ];

        $neutralComments = [
            'Sản phẩm bình thường, đúng như mô tả.',
            'Chất lượng trung bình, giá cả hợp lý.',
            'Dùng được, không có gì đặc biệt.',
            'Tạm ổn, chưa dùng lâu nên chưa đánh giá được.',
            'Sản phẩm như mong đợi, không hơn không kém.',
            'Chất lượng OK, giao hàng đúng hẹn.',
            'Dùng tạm được, chờ xem độ bền thế nào.',
            'Bình thường thôi, không có gì nổi bật.',
        ];

        $averageComments = [
            'Sản phẩm tạm được nhưng chưa thực sự ấn tượng.',
            'Chất lượng trung bình, có thể cải thiện thêm.',
            'Dùng được nhưng cảm giác hơi nhẹ.',
            'Giao hàng hơi chậm, sản phẩm bình thường.',
            'Giá hơi cao so với chất lượng.',
            'Sản phẩm OK nhưng đóng gói có thể tốt hơn.',
            'Tạm ổn, chưa thấy sự khác biệt nhiều.',
            'Dùng được, nhưng mong đợi sẽ tốt hơn.',
        ];

        $negativeComments = [
            'Sản phẩm không đúng như mô tả, hơi thất vọng.',
            'Chất lượng chưa tốt, không như kỳ vọng.',
            'Giao hàng chậm, sản phẩm có vài khuyết điểm.',
            'Không phù hợp với xe của mình, phải đổi trả.',
            'Chất lượng tệ hơn mong đợi, không recommend.',
            'Sản phẩm có lỗi nhỏ, dịch vụ chăm sóc chậm.',
            'Không đáng đồng tiền, sẽ không mua lại.',
            'Chất lượng kém, không như hình ảnh.',
        ];

        // Tạo review cho các đơn hàng đã hoàn thành
        foreach ($completedOrders as $order) {
            $orderDetails = OrderDetail::where('order_id', $order->id)->get();

            foreach ($orderDetails as $detail) {
                if ($faker->boolean(70)) {
                    $rating = $this->getRandomRating($faker);
                    $comment = $this->getCommentByRating($faker, $rating, $positiveComments, $goodComments, $neutralComments, $averageComments, $negativeComments);

                    $reviewDate = $order->completed_at 
                        ? $faker->dateTimeBetween($order->completed_at, $order->completed_at->addDays(30))
                        : $faker->dateTimeBetween($order->created_at, $order->created_at->addDays(30));

                    try {
                        Review::create([
                            'user_id' => $order->user_id,
                            'product_id' => $detail->product_id,
                            'order_id' => $order->id,
                            'rating' => $rating,
                            'comment' => $comment,
                            'created_at' => $reviewDate,
                            'updated_at' => $reviewDate,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        $products = Product::all();
        $users = User::all();
        $orders = Order::all();

        foreach ($products as $product) {
            $currentReviewCount = Review::where('product_id', $product->id)->count();
            $needMoreReviews = max(0, 10 - $currentReviewCount);

            for ($i = 0; $i < $needMoreReviews; $i++) {
                $user = $faker->randomElement($users);
                $order = $faker->randomElement($orders);
                $rating = $this->getRandomRating($faker);
                $comment = $this->getCommentByRating($faker, $rating, $positiveComments, $goodComments, $neutralComments, $averageComments, $negativeComments);

                $reviewDate = $faker->dateTimeBetween('-6 months', 'now');

                try {
                    Review::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'order_id' => $order->id,
                        'rating' => $rating,
                        'comment' => $faker->boolean(85) ? $comment : null, // 15% không có comment
                        'created_at' => $reviewDate,
                        'updated_at' => $reviewDate,
                    ]);
                } catch (\Exception $e) {
                    for ($retry = 0; $retry < 3; $retry++) {
                        $user = $faker->randomElement($users);
                        $order = $faker->randomElement($orders);
                        
                        try {
                            Review::create([
                                'user_id' => $user->id,
                                'product_id' => $product->id,
                                'order_id' => $order->id,
                                'rating' => $rating,
                                'comment' => $faker->boolean(85) ? $comment : null,
                                'created_at' => $reviewDate,
                                'updated_at' => $reviewDate,
                            ]);
                            break;
                        } catch (\Exception $e) {
                            if ($retry === 2) break;
                        }
                    }
                }
            }
        }
    }

    private function getRandomRating($faker)
    {
        $rand = $faker->numberBetween(1, 100);
        
        if ($rand <= 50) return 5;      // 50%
        if ($rand <= 75) return 4;      // 25%
        if ($rand <= 90) return 3;      // 15%
        if ($rand <= 97) return 2;      // 7%
        return 1;                       // 3%
    }

    private function getCommentByRating($faker, $rating, $positiveComments, $goodComments, $neutralComments, $averageComments, $negativeComments)
    {
        switch ($rating) {
            case 5:
                return $faker->randomElement($positiveComments);
            case 4:
                return $faker->randomElement($goodComments);
            case 3:
                return $faker->randomElement($neutralComments);
            case 2:
                return $faker->randomElement($averageComments);
            case 1:
                return $faker->randomElement($negativeComments);
            default:
                return $faker->randomElement($neutralComments);
        }
    }
} 