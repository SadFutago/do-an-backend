<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $files = collect(Storage::disk('public')->allFiles('default/Images'))
            ->filter(function ($file) {
                return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
            })->values()->all();

        $brands = [
            'Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan',
            'BMW', 'Mercedes-Benz', 'Volkswagen', 'Audi', 'Lexus',
            'Hyundai', 'Kia', 'Mazda', 'Mitsubishi', 'Subaru',
            'Suzuki', 'Porsche', 'Jaguar', 'Land Rover', 'Volvo',
        ];

        foreach ($brands as $brand) {
            $category = Category::create([
                'name' => $brand,
                'type' => 'brand',
            ]);

            Image::create([
                'category_id' => $category->id,
                'filename' => 'thumbnail_' . Str::random(5),
                'path' => '/storage/' . $faker->randomElement($files),
                'is_thumbnail' => true,
                'size' => rand(100000, 800000),
                'mime_type' => 'image/jpeg',
            ]);

            $images = [];
            for ($j = 0; $j < 4; $j++) {
                $images[] = [
                    'category_id' => $category->id,
                    'filename' => 'category_image_' . Str::random(5),
                    'path' => '/storage/' . $faker->randomElement($files),
                    'is_thumbnail' => false,
                    'size' => rand(100000, 800000),
                    'mime_type' => 'image/jpeg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Image::insert($images);
        }

        $parts = [
            "Phụ tùng động cơ", "Phụ tùng gầm xe", "Phụ tùng thân & vỏ", "Phụ tùng điện", "Phụ tùng điều hòa", "Hệ thống phanh",
            "Hệ thống treo", "Hệ thống làm mát", "Hệ thống nhiên liệu", "Hệ thống chiếu sáng", "Phụ tùng lốp xe",
            "Gương và kính xe", "Bộ ly hợp và hộp số", "Hệ thống lái", "Hệ thống xả", "Phụ kiện trang trí nội thất",
            "Phụ kiện trang trí ngoại thất", "Phụ kiện công nghệ và tiện ích"
        ];

        foreach ($parts as $part) {
            $category = Category::create([
                'name' => $part,
                'type' => 'part',
            ]);

            
            Image::create([
                'category_id' => $category->id,
                'filename' => 'thumbnail_' . Str::random(5),
                'path' => '/storage/' . $faker->randomElement($files),
                'is_thumbnail' => true,
                'size' => rand(100000, 800000),
                'mime_type' => 'image/jpeg',
            ]);

            $images = [];
            for ($j = 0; $j < 4; $j++) {
                $images[] = [
                    'category_id' => $category->id,
                    'filename' => 'category_image_' . Str::random(5),
                    'path' => '/storage/' . $faker->randomElement($files),
                    'is_thumbnail' => false,
                    'size' => rand(100000, 800000),
                    'mime_type' => 'image/jpeg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Image::insert($images);
        }
        
    }
}
