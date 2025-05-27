<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Models\Image;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    private function generateArticleContent($faker, array $files): string
    {
        $content = '';
        $j = 0;

        for ($section = 0; $section < 3; $section++) {
            $content .= '<div><b>' . $faker->sentence(10) . '</b></div>';

            $numSentences = rand(3, 6);
            for ($i = 0; $i < $numSentences; $i++) {
                $content .= '<p>' . $faker->sentence(rand(8, 15)) . '</p>';
            }
            $randomImages = collect($files)->shuffle()->take(2);
            foreach ($randomImages as $image) {
                $filename = $j;
                $path = basename($image);
                $content .= '<img alt="' . $filename . '" src="/storage/default/Images/' . $path . '">';
                $j++;
            }
        }

        return $content;
    }

    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $userIds = User::pluck('id')->toArray();

        $categories = [
            [
                'name' => 'Tin Nổi Bật',
                'type' => 'article',
                'description' => 'Các tin tức nổi bật và quan trọng về Sanhua Auto Parts',
            ],
            [
                'name' => 'Tin Công Ty',
                'type' => 'article',
                'description' => 'Thông tin về hoạt động và phát triển của công ty Sanhua Auto Parts',
            ],
            [
                'name' => 'Tin Bán Hàng',
                'type' => 'article',
                'description' => 'Thông tin về sản phẩm, khuyến mãi và hoạt động bán hàng',
            ],
        ];
        $categoryIds = [];
        foreach ($categories as $category) {
            $createdCategory = Category::create($category);
            $categoryIds[] = $createdCategory->id;
        }

        $files = collect(Storage::disk('public')->allFiles('default/Images'))
            ->filter(function ($file) {
                return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
            })->values()->all();

        for ($i = 0; $i < 50; $i++) {
            $title = $faker->sentence(6);
            $slug = Str::slug($title);
            
            $article = Article::create([
                'title' => $title,  
                'slug' => $slug,
                'highlight' => $faker->sentence,
                'author' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'publish_date' => $faker->dateTimeBetween('-6 months', '+1 week')->format('Y-m-d'),
                'content' => $this->generateArticleContent($faker, $files),
                'category_id' => $faker->randomElement($categoryIds),
            ]);

            Image::create([
                'article_id' => $article->id,
                'filename' => 7,
                'path' => '/storage/' . $faker->randomElement($files),
                'is_thumbnail' => true,
                'size' => rand(100000, 800000),
                'mime_type' => 'image/jpeg',
            ]);

            $images = [];
            for ($j = 0; $j < 7; $j++) {
                $images[] = [
                    'article_id' => $article->id,
                    'filename' => $j,
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
