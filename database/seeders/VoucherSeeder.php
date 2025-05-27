<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voucher;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $userIds = User::pluck('id')->toArray();

        $voucherData = [
            [
                'code' => 'WELCOME10',
                'value' => 10000,
                'usage_limit' => 100,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(30),
            ],
            [
                'code' => 'NEWYEAR2024',
                'value' => 50000,
                'usage_limit' => 50,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(60),
            ],
            [
                'code' => 'SUMMER20',
                'value' => 20000,
                'usage_limit' => 200,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(45),
            ],
            [
                'code' => 'FLASHSALE',
                'value' => 15000,
                'usage_limit' => 500,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(15),
            ],
            [
                'code' => 'BLACKFRIDAY',
                'value' => 100000,
                'usage_limit' => 30,
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->addDays(90),
            ],
        ];

        foreach ($voucherData as $data) {
            Voucher::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'code' => $data['code'],
                'value' => $data['value'],
                'usage_limit' => $data['usage_limit'],
                'used_count' => $faker->numberBetween(0, min($data['usage_limit'], 10)),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        }

        // Tạo thêm 10 voucher ngẫu nhiên
        for ($i = 0; $i < 10; $i++) {
            $code = strtoupper($faker->lexify('????') . $faker->numberBetween(10, 99));
            
            Voucher::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'code' => $code,
                'value' => $faker->randomElement([5000, 10000, 15000, 20000, 25000, 30000, 50000]),
                'usage_limit' => $faker->numberBetween(10, 500),
                'used_count' => $faker->numberBetween(0, 5),
                'start_date' => $faker->dateTimeBetween('-30 days', 'now'),
                'end_date' => $faker->dateTimeBetween('now', '+90 days'),
            ]);
        }
    }
} 