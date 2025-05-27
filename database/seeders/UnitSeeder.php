<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Cái', 'description' => 'Đơn vị từng món riêng lẻ'],
            ['name' => 'Chiếc', 'description' => 'Đơn vị từng món, tương tự như "Cái"'],
            ['name' => 'Bộ', 'description' => 'Gồm nhiều món thành phần trong 1 gói'],
            ['name' => 'Thùng', 'description' => 'Số lượng lớn, chứa nhiều đơn vị nhỏ'],
            ['name' => 'Lít', 'description' => 'Dùng cho chất lỏng như dầu nhớt, nước làm mát'],
            ['name' => 'Kg', 'description' => 'Khối lượng sản phẩm'],
            ['name' => 'Cuộn', 'description' => 'Thường dùng cho dây, băng keo, v.v.'],
            ['name' => 'Ống', 'description' => 'Dạng chất lỏng hoặc gel, ví dụ keo, mỡ bôi trơn'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
