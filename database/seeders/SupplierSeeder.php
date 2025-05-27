<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $prefixes = ['032', '033', '034', '035', '036', '037', '038', '039', '070', '079', '077', '076', '078', '083', '084', '085', '081', '082']; 

        for ($i = 0; $i < 20; $i++) {
            $company = $faker->company;
            $email = Str::slug($company, '') . $i . '@gmail.com';

            Supplier::create([
                'name' => $company,
                'email' => strtolower($email),
                'phone' => $faker->randomElement($prefixes) . $faker->numberBetween(1000000, 9999999),
                'address' => $faker->buildingNumber . ', ' . $faker->streetName . ', ' . $faker->city . ', ' . $faker->province,
            ]);
        }
    }
}
