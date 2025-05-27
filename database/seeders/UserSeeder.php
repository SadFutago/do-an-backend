<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Image;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $prefixes = ['032', '033', '034', '035', '036', '037', '038', '039', '070', '079', '077', '076', '078', '083', '084', '085', '081', '082'];
        $faker = Faker::create('vi_VN');
        $roleIds = Role::pluck('id')->toArray();

        $files = collect(Storage::disk('public')->allFiles('default/Images'))
            ->filter(function ($file) {
                return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
            })->values()->all();

        for ($i = 0; $i < 20; $i++) {
            $name = $faker->lastName . ' ' . $faker->firstName;
            $email = Str::slug($name, '.') . $i . '@gmail.com';

            $avatar = Image::create([
                'filename' => 'thumbnail_' . Str::random(5),
                'path' => '/storage/' . $faker->randomElement($files),
                'is_thumbnail' => true,
                'size' => rand(100000, 800000),
                'mime_type' => 'image/jpeg',
            ]);

            User::create([
                'username' => 'test' . $i,
                'password' => bcrypt('test' . $i),
                'name' => $name,
                'email' => strtolower($email),
                'phone' => $faker->randomElement($prefixes) . $faker->numberBetween(1000000, 9999999),
                'date_of_birth' => $faker->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
                'role_id' => $faker->randomElement($roleIds),
                'avatar_id' => $avatar->id,
            ]);
        }
        User::create([
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
    }
}
