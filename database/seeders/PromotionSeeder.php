<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $userIds = User::pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            $startDate = $faker->dateTimeBetween('-1 month', '+2 weeks')->format('Y-m-d');
            $endDate = Carbon::parse($startDate)->addDays($faker->numberBetween(7, 30))->format('Y-m-d');
            
            Promotion::create([
                'created_by' => $faker->optional(0.8)->randomElement($userIds),
                'approved_by' => $faker->optional(0.5)->randomElement($userIds),
                'name' => $faker->randomElement(['Khuyến mãi hè', 'Giảm giá cuối năm', 'Đón xuân mới', 'Black Friday']) . ' ' . $faker->year,
                'discount_percent' => $faker->numberBetween(5, 50),
                'max_discount_amount' => $faker->optional(0.7)->numberBetween(100000, 500000),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $faker->randomElement([0, 1, 2]),
            ]);
        }
    }
}
