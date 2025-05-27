<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use App\Models\{ Supplier, User, Product, Category };
use App\Models\{ Import, ImportDetail, Inventory, Location, Export, ExportDetail, Disposal, DisposalDetail, Check, CheckDetail, Cancel, CancelDetail };

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $supplierIds = Supplier::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();

        $locations = [];
        for ($i = 0; $i < 20; $i++) {
            $locations[] = Location::create([
                'zone' => $faker->randomElement(['A', 'B', 'C', 'D']),
                'aisle' => $faker->numberBetween(1, 10),
                'rack' => $faker->numberBetween(1, 5),
                'shelf' => $faker->numberBetween(1, 5),
                'bin' => $faker->numberBetween(1, 10),
                'status' => $faker->numberBetween(0, 2),
                'category_id' => $faker->randomElement($categoryIds),
            ]);
        }

        for ($i = 0; $i < 25; $i++) {
            $import = Import::create([
                'supplier_id' => $faker->randomElement($supplierIds),
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'deliverer' => $faker->name,
                'address' => $faker->address,
                'date' => $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
                'total_amount' => 0,
            ]);
            
            $totalAmount = 0;
            for ($j = 0; $j < 5; $j++) {
                $quantity = $faker->numberBetween(10, 100);
                $price = $faker->numberBetween(10000, 100000);
                $amount = $quantity * $price;
                $totalAmount += $amount;
                
                ImportDetail::create([
                    'import_id' => $import->id,
                    'product_id' => $faker->randomElement($productIds),
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
                
                $inventory = Inventory::create([
                    'batch_number' => strtoupper($faker->bothify('??##??##')),
                    'quantity' => $quantity,
                    'manufacture_date' => $faker->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
                    'expiry_date' => $faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
                    'product_id' => $faker->randomElement($productIds),
                    'import_id' => $import->id,
                ]);
                
                $location = $faker->randomElement($locations);
                DB::table('inventory_location')->insert([
                    'inventory_id' => $inventory->id,
                    'location_id' => $location->id,
                    'quantity' => $quantity,
                ]);
            }
            
            $import->update(['total_amount' => $totalAmount]);
        }

        for ($i = 0; $i < 20; $i++) {
            $export = Export::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'date' => $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
                'receiver' => $faker->name,
                'address' => $faker->randomElement(['Phòng Kế toán', 'Phòng Nhân sự', 'Phòng Kinh doanh', 'Phòng Kỹ thuật']),
                'reason' => $faker->randomElement(['Sử dụng nội bộ', 'Bán hàng', 'Xuất trả nhà cung cấp']),
                'total_amount' => 0,
            ]);
            
            $totalAmount = 0;
            $inventories = Inventory::inRandomOrder()->limit(3)->get();
            
            foreach ($inventories as $inventory) {
                $quantity = $faker->numberBetween(1, min(20, $inventory->quantity));
                $price = $faker->numberBetween(10000, 100000);
                $amount = $quantity * $price;
                $totalAmount += $amount;
                
                ExportDetail::create([
                    'export_id' => $export->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
                
                $inventory->decrement('quantity', $quantity);
                
                $location = DB::table('inventory_location')
                    ->where('inventory_id', $inventory->id)
                    ->first();
                
                if ($location) {
                    DB::table('inventory_location')
                        ->where('inventory_id', $inventory->id)
                        ->where('location_id', $location->location_id)
                        ->decrement('quantity', $quantity);
                }
            }
            
            $export->update(['total_amount' => $totalAmount]);
        }

        for ($i = 0; $i < 10; $i++) {
            $disposal = Disposal::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'date' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'reason' => $faker->randomElement(['Hết hạn sử dụng', 'Hư hỏng', 'Lỗi kỹ thuật']),
                'total_amount' => 0,
            ]);
            
            $totalAmount = 0;
            $inventories = Inventory::where('expiry_date', '<', now()->addMonth())
                ->inRandomOrder()
                ->limit(2)
                ->get();
            
            foreach ($inventories as $inventory) {
                $quantity = $faker->numberBetween(1, min(10, $inventory->quantity));
                $price = $faker->numberBetween(10000, 50000);
                $amount = $quantity * $price;
                $totalAmount += $amount;
                
                DisposalDetail::create([
                    'disposal_id' => $disposal->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'method' => $faker->randomElement(['Tiêu hủy', 'Bán phế liệu', 'Tái chế']),
                ]);
                
                $inventory->decrement('quantity', $quantity);
                
                $location = DB::table('inventory_location')
                    ->where('inventory_id', $inventory->id)
                    ->first();
                
                if ($location) {
                    DB::table('inventory_location')
                        ->where('inventory_id', $inventory->id)
                        ->where('location_id', $location->location_id)
                        ->decrement('quantity', $quantity);
                }
            }
            
            $disposal->update(['total_amount' => $totalAmount]);
        }

        for ($i = 0; $i < 5; $i++) {
            $check = Check::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'date' => $faker->dateTimeBetween('-2 weeks', 'now')->format('Y-m-d'),
                'total_amount' => 0,
            ]);
            
            $totalAmount = 0;
            $inventories = Inventory::inRandomOrder()->limit(5)->get();
            
            foreach ($inventories as $inventory) {
                $price = $faker->numberBetween(10000, 100000);
                $amount = $inventory->quantity * $price;
                $totalAmount += $amount;
                
                CheckDetail::create([
                    'check_id' => $check->id,
                    'inventory_id' => $inventory->id,
                    'quality' => $faker->randomElement(['Còn tốt 100%', 'Kém phẩm chất', 'Mất phẩm chất']),
                    'quantity' => $inventory->quantity,
                    'price' => $price,
                ]);
            }
            
            $check->update(['total_amount' => $totalAmount]);
        }

        for ($i = 0; $i < 5; $i++) {
            $cancel = Cancel::create([
                'created_by' => $faker->randomElement($userIds),
                'approved_by' => $faker->randomElement($userIds),
                'date' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'reason' => $faker->randomElement(['Hư hỏng', 'Lỗi sản xuất', 'Không đạt chất lượng']),
                'total_amount' => 0,
            ]);
            
            $totalAmount = 0;
            $inventories = Inventory::inRandomOrder()->limit(2)->get();
            
            foreach ($inventories as $inventory) {
                $quantity = $faker->numberBetween(1, min(5, $inventory->quantity));
                $price = $faker->numberBetween(10000, 50000);
                $amount = $quantity * $price;
                $totalAmount += $amount;
                
                CancelDetail::create([
                    'cancel_id' => $cancel->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'method' => $faker->randomElement(['Tiêu hủy', 'Bán phế liệu']),
                ]);
                
                $inventory->decrement('quantity', $quantity);
                
                $location = DB::table('inventory_location')
                    ->where('inventory_id', $inventory->id)
                    ->first();
                
                if ($location) {
                    DB::table('inventory_location')
                        ->where('inventory_id', $inventory->id)
                        ->where('location_id', $location->location_id)
                        ->decrement('quantity', $quantity);
                }
            }
            
            $cancel->update(['total_amount' => $totalAmount]);
        }
    }
}