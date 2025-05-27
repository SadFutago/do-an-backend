<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Image;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $promotionIds = Promotion::pluck('id')->toArray();
        $supplierIds = Supplier::pluck('id')->toArray();
        $unitIds = Unit::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();

        $products = [
            'Van EGR Hyundai Tucson 2.0L đời 2018-2022', 'Van EGR Toyota Land Cruiser V8 động cơ diesel 4.5L', 'Van EGR Honda CRV 1.5 Turbo đời 2020-2023', 'Van EGR Ford Ranger Wildtrak 2.0L Bi-Turbo', 'Van EGR Mazda CX-5 2.5L Skyactiv-G Premium',
            'Van PCV Honda Civic 1.5L VTEC Turbo RS', 'Van PCV Toyota Camry 2.5Q hybrid động cơ A25A-FXS', 'Van PCV Mitsubishi Xpander 1.5L MIVEC đời 2020', 'Van PCV Hyundai Santa Fe 2.2L CRDi dầu cao cấp', 'Van PCV Kia Sorento Signature 2.2D máy dầu',
            'Van hằng nhiệt Toyota Innova Cross 2.0L hybrid đời 2023', 'Van hằng nhiệt Lexus LX 570 V8 5.7L cao cấp', 'Van hằng nhiệt BMW X5 xDrive40i động cơ B58', 'Van hằng nhiệt Mercedes C300 AMG Line W206 đời 2022', 'Van hằng nhiệt Audi Q5 45 TFSI quattro S-line',
            'Van điều áp nhiên liệu Mazda 3 2.0L Skyactiv-G Premium', 'Van điều áp nhiên liệu Ford Everest Titanium 2.0L', 'Van điều áp nhiên liệu Nissan Terra 2.5L V4 VL 4WD', 'Van điều áp nhiên liệu Isuzu D-Max 3.0 Ddi BluePower', 'Van điều áp nhiên liệu Suzuki XL7 1.5L GLX',
            'Bugi NGK Laser Iridium Toyota Hilux Revo 2.8 GD', 'Bugi NGK Ruthenium HX Mazda BT-50 3.2L Diesel', 'Bugi NGK G-Power Platinum Honda Brio 1.2L i-VTEC', 'Bugi NGK LPG Laser Line Mitsubishi Outlander 2.4 MIVEC', 'Bugi NGK Racing Hyundai Veloster N 2.0T',
            'Bugi Denso Iridium TT Honda HR-V 1.8L i-VTEC', 'Bugi Denso Twin Tip Toyota Rush 1.5L Dual VVT-i', 'Bugi Denso Platinum Mitsubishi Pajero Sport 2.4D MIVEC', 'Bugi Denso Iridium Power Lexus RX350 V6 3.5L', 'Bugi Denso Long Life Subaru Forester 2.0i-S EyeSight',
            'Dây curoa tổng Toyota Fortuner 2.7L đời 2017-2022', 'Dây curoa tổng Honda Accord 2.4L động cơ K24', 'Dây curoa tổng Nissan Navara NP300 2.5L dầu', 'Dây curoa tổng Chevrolet Colorado High Country 2.5L', 'Dây curoa tổng Peugeot 5008 1.6L THP turbo',
            'Dây curoa cam Volkswagen Tiguan Allspace 2.0 TSI', 'Dây curoa cam Volvo XC60 T8 Twin Engine Hybrid', 'Dây curoa cam Mitsubishi Triton Athlete 2.4L MIVEC', 'Dây curoa cam Ford Ranger Raptor 2.0L Bi-Turbo', 'Dây curoa cam Kia Carnival 2.2L CRDi Signature',
            'Dầu động cơ Castrol EDGE 5W-30 Advanced Fully Synthetic', 'Dầu động cơ Castrol Magnatec Stop-Start 5W-30 A5', 'Dầu động cơ Castrol GTX ULTRACLEAN 10W-40 A3/B4', 'Dầu động cơ Castrol Power 1 Racing 4T 10W-50', 'Dầu động cơ Castrol CRB Turbo G3 15W-40 CI-4/SL',
            'Dầu động cơ Mobil 1 ESP Formula 5W-30 Advanced Synthetic', 'Dầu động cơ Mobil Super 3000 X1 Formula FE 5W-30', 'Dầu động cơ Mobil Delvac MX ESP 15W-40 Diesel', 'Dầu động cơ Mobil Super 2000 X2 10W-40 Semi Synthetic', 'Dầu động cơ Mobil Delvac City Logistics P 5W-30',
            'Gạt mưa Bosch Aerotwin Multi-Clip Toyota Land Cruiser Prado', 'Gạt mưa Bosch Clear Advantage Ford Ranger XLS AT 4x2', 'Gạt mưa Bosch Aerofit Frameless Honda CRV L 1.5 Turbo', 'Gạt mưa Bosch Evolution Beam Hyundai Tucson 2.0 Premium', 'Gạt mưa Bosch Icon Wiper Blade Mazda CX-8 2.5 Premium AWD',
            'Lọc gió động cơ K&N Toyota RAV4 2.5L Hybrid Adventure', 'Lọc gió động cơ Mann-Filter Honda HRV RS 1.5 Turbo', 'Lọc gió động cơ Sakura Mitsubishi Pajero Sport GT Premium 4x4', 'Lọc gió động cơ Denso Hyundai Palisade 3.8L V6 Exclusive', 'Lọc gió động cơ Bosch Kia Sportage 2.0 Signature AWD',
            'Lọc dầu động cơ Mobil 1 Ford Everest 2.0L Bi-Turbo Titanium', 'Lọc dầu động cơ Toyota Genuine Camry 2.5Q 2022-2023', 'Lọc dầu động cơ Mahle BMW X1 sDrive18i xLine Edition', 'Lọc dầu động cơ WIX Mercedes Benz GLC 300 4MATIC AMG', 'Lọc dầu động cơ Fram Ultra Synthetic Audi Q3 Sportback 40 TFSI',
            'Lọc nhiên liệu diesel Fleetguard Toyota Hilux 2.8G 4x4 AT', 'Lọc nhiên liệu diesel Racor Ford Ranger Wildtrak 2.0L 4x4 AT', 'Lọc nhiên liệu diesel Sakura Isuzu MU-X 1.9L Diesel Premium', 'Lọc nhiên liệu diesel Baldwin Mitsubishi Pajero Sport 2.4D MIVEC', 'Lọc nhiên liệu diesel Cummins Mazda BT-50 1.9L MT 4x2',
            'Phụ kiện đèn pha LED Philips Ultinon Pro9000 cho Toyota Fortuner', 'Phụ kiện đèn pha LED Osram Night Breaker H7 cho Ford Ranger', 'Phụ kiện đèn pha LED PIAA Hyper Arros cho Honda CRV', 'Phụ kiện đèn pha LED Lazer Triple-R 750 cho Nissan Terra', 'Phụ kiện đèn pha LED HELLA Performance cho Mazda BT-50',
            'Bình ắc quy Amaron Pro 95D31R cho Toyota Land Cruiser', 'Bình ắc quy GS Premium N120 cho Mercedes GLS 450 4MATIC', 'Bình ắc quy Bosch S5 A13 AGM cho BMW 530i M Sport', 'Bình ắc quy Varta Blue Dynamic E11 cho Audi Q7 3.0 TFSI', 'Bình ắc quy Delkor LN3 (90D23L) cho Lexus ES 350',
            'Phanh đĩa trước Brembo Gran Turismo cho Subaru WRX STI', 'Phanh đĩa trước ATE PowerDisc cho Volkswagen Tiguan Allspace', 'Phanh đĩa trước Ferodo Premier cho Mazda 6 2.5L Premium', 'Phanh đĩa trước EBC Brakes USR Series cho Honda Civic Type R', 'Phanh đĩa trước DBA 4000 Series T3 Slotted cho Ford Mustang GT',
            'Má phanh sau Akebono Euro Ultra-Premium Ceramic cho Lexus NX 300', 'Má phanh sau Textar epad cho BMW X3 xDrive20i xLine', 'Má phanh sau TRW cho Mercedes C200 Avantgarde', 'Má phanh sau Bendix General CT cho Toyota Alphard 2.5L', 'Má phanh sau Jurid Premium Quality cho Volvo S60 T5 R-Design',
            'Giảm xóc KYB Excel-G cho Honda City RS 1.5L Turbo', 'Giảm xóc Bilstein B6 Performance cho Ford Everest Titanium Plus', 'Giảm xóc Monroe Original cho Hyundai Creta 1.5L Premium', 'Giảm xóc Tokico D-Spec cho Suzuki Swift Sport', 'Giảm xóc Sachs Advantage cho Kia Carnival 2.2L CRDi',
            'Thanh giằng cân bằng Whiteline cho Subaru Forester 2.0i-S EyeSight', 'Thanh giằng cân bằng Hardrace cho Honda Civic 1.5L RS', 'Thanh giằng cân bằng Cusco cho Toyota GR Yaris 1.6L Turbo', 'Thanh giằng cân bằng Megan Racing cho Mazda 3 2.0L Premium', 'Thanh giằng cân bằng Ultra Racing cho Hyundai Kona 1.6T',
            'Càng A Ford Ranger Raptor phiên bản 2023 chính hãng', 'Càng A Toyota Land Cruiser 300 VX-R hàng nhập khẩu', 'Càng A Mitsubishi Pajero Sport GT Premium 4WD chính hãng', 'Càng A Isuzu D-Max V-Cross 3.0 Prestige 4x4 nhập khẩu', 'Càng A Nissan Navara Pro-4X 2.5L YD25 hàng chính hãng',
            'Rotuyn lái ngoài Lemförder cho Mercedes GLE 450 4MATIC Coupe', 'Rotuyn lái ngoài 555 cho Toyota Vios 1.5G CVT 2023', 'Rotuyn lái ngoài Febi Bilstein cho BMW X5 xDrive40i M Sport', 'Rotuyn lái ngoài CTR cho Hyundai Santa Fe 2.5L Premium', 'Rotuyn lái ngoài MOOG cho Ford Territory Titanium 1.5L EcoBoost',
            'Cao su chân máy Hutchinson cho Peugeot 3008 GT Line 1.6L Turbo', 'Cao su chân máy Febest cho Honda Accord 1.5L Turbo', 'Cao su chân máy Corteco cho Mazda CX-8 2.5L Premium AWD', 'Cao su chân máy Lemförder cho Audi A4 2.0 TFSI', 'Cao su chân máy OEM cho Toyota Corolla Cross 1.8 HV',
            'Phụ tùng hộp số tự động ZF cho BMW Series 5 530i M Sport', 'Phụ tùng hộp số tự động AISIN cho Toyota Camry 2.5HV đời 2023', 'Phụ tùng hộp số tự động JATCO cho Nissan X-Trail 2.0L Premium', 'Phụ tùng hộp số tự động Allison cho Chevrolet Silverado HD 2500', 'Phụ tùng hộp số tự động Magna Powertrain cho Mercedes GLC 300 AMG',
            'Dầu hộp số tự động AMSOIL Signature Series cho Lexus LX 600', 'Dầu hộp số tự động Liqui Moly Top Tec cho Audi Q7 3.0 TFSI', 'Dầu hộp số tự động Pentosin cho Volkswagen Teramont 2.0 TSI', 'Dầu hộp số tự động Ravenol J2/S cho BMW X7 xDrive40i', 'Dầu hộp số tự động Motul DCTF cho Mercedes A250 AMG Line',
            'Gioăng đại tu động cơ Ajusa cho Mitsubishi Triton Athlete', 'Gioăng đại tu động cơ Victor Reinz cho Ford Ranger XLT Limited', 'Gioăng đại tu động cơ Elring cho Mercedes E300 AMG Line', 'Gioăng đại tu động cơ Nippon Leakless cho Toyota Hilux 2.8G', 'Gioăng đại tu động cơ Corteco cho BMW X3 xDrive20i xLine',
            'Xi lanh phanh chính ATE cho Volkswagen Tiguan Allspace', 'Xi lanh phanh chính TRW cho Hyundai Tucson 2.0D Signature', 'Xi lanh phanh chính ADVICS cho Toyota Land Cruiser Prado', 'Xi lanh phanh chính Valeo cho Peugeot 5008 1.6L THP', 'Xi lanh phanh chính Akebono cho Honda Odyssey 2.4L',
            'Bơm nước làm mát GMB cho Honda CRV 1.5 Turbo đời 2021', 'Bơm nước làm mát AISIN cho Toyota Fortuner 2.8V 4x4 AT', 'Bơm nước làm mát Hepu cho BMW 520i Sport Line', 'Bơm nước làm mát Continental cho Mercedes GLC 200 Exclusive', 'Bơm nước làm mát Graf cho Audi A4 40 TFSI Premium',
            'Két nước làm mát Denso cho Honda Civic RS 1.5L Turbo', 'Két nước làm mát Nissens cho Kia Sportage Signature AWD', 'Két nước làm mát Valeo cho Peugeot 3008 GT Line', 'Két nước làm mát NRF cho Volkswagen Tiguan Elegance', 'Két nước làm mát Hella cho Mercedes C-Class C300 AMG',
            'Két điều hòa Koyorad cho Toyota RAV4 2.5L Hybrid', 'Két điều hòa Denso cho Mitsubishi Outlander 2.4 MIVEC', 'Két điều hòa Nissens cho Ford Territory Titanium 1.5L', 'Két điều hòa Valeo cho Kia Carnival 2.2L Signature', 'Két điều hòa Delphi cho Hyundai Palisade 3.8L V6',
            'Lọc điều hòa Bosch Carbon Active cho Mazda CX-5 2.5L Premium', 'Lọc điều hòa Mann-Filter cho Mercedes GLA 200 AMG Line', 'Lọc điều hòa Denso cho Toyota Corolla Cross 1.8 HV', 'Lọc điều hòa Mahle cho BMW X1 sDrive18i xLine', 'Lọc điều hòa Sakura cho Honda HRV 1.5L RS Turbo',
            'Quạt két nước Valeo cho Peugeot 2008 1.2L PureTech GT Line', 'Quạt két nước SPAL cho Ford Bronco Sport 2.0L EcoBoost', 'Quạt két nước Nissens cho Volkswagen T-Cross 1.0 TSI', 'Quạt két nước NRF cho Audi Q3 Sportback 40 TFSI', 'Quạt két nước Denso cho Lexus IS 300 Luxury',
            'Ốc bánh xe BBS cho BMW M3 Competition xDrive', 'Ốc bánh xe Rays cho Toyota GR Yaris Circuit Pack', 'Ốc bánh xe Muteki cho Honda Civic Type R FL5', 'Ốc bánh xe Gorilla cho Ford Mustang Mach 1', 'Ốc bánh xe McGard cho Audi RS6 Avant Performance',
            'Mâm độ Vossen HF-5 cho Mercedes AMG GT 53 4-Door Coupe', 'Mâm độ Advan Racing GT cho Nissan GTR R35 Nismo', 'Mâm độ BBS FI-R cho BMW M4 Competition G82', 'Mâm độ Work Emotion ZR10 cho Subaru WRX STI S209', 'Mâm độ Rays Gram Lights 57DR cho Honda Civic Type R',
            'Lốp Michelin Pilot Sport 4 SUV 265/50R20 cho Mercedes GLE Coupe', 'Lốp Bridgestone Alenza A/S 02 275/45R21 cho Audi Q8', 'Lốp Continental ExtremeContact DWS06 Plus 245/40ZR19 cho BMW M340i', 'Lốp Pirelli P Zero PZ4 295/30ZR20 cho Porsche 911 Carrera', 'Lốp Goodyear Eagle F1 Asymmetric 6 235/45R18 cho Lexus ES 250',
            'Bộ chia nguồn âm thanh Audison Bit One HD Virtuoso cho Mercedes S-Class', 'Bộ chia nguồn âm thanh Helix DSP Ultra cho BMW 7 Series', 'Bộ chia nguồn âm thanh Mosconi 8to12 Aerospace cho Audi A8', 'Bộ chia nguồn âm thanh Arc Audio PS8-Pro cho Lexus LS 500h', 'Bộ chia nguồn âm thanh Zapco ADSP-Z8 IV-8 cho Range Rover Autobiography',
            'Loa Mid-range Focal Utopia M 3.5WM cho Mercedes S-Class Maybach', 'Loa Mid-range Dynaudio Esotar2 430 cho BMW 7 Series Individual', 'Loa Mid-range Morel Supremo 602 cho Audi A8 L', 'Loa Mid-range Scan-Speak Illuminator 12MU cho Lexus LX 600', 'Loa Mid-range Accuton C168-6-RW cho Range Rover SV Autobiography',
            'Sub điện Focal Utopia M Subwoofer 6WM cho Mercedes S-Class', 'Sub điện JL Audio 12W7AE-3 cho Cadillac Escalade ESV', 'Sub điện Alpine R-W12D2 cho GMC Yukon Denali', 'Sub điện Rockford Fosgate T1D212 cho Ford F-150 Raptor', 'Sub điện Kicker Q-Class L7 12-inch cho Chevrolet Silverado High Country',
            'Bọc vô lăng MOMO Quark Performance cho BMW M3 Competition', 'Bọc vô lăng Sparco R375 cho Ford Focus RS', 'Bọc vô lăng Nardi Deep Corn cho Mazda MX-5 Miata', 'Bọc vô lăng OMP Trecento cho Subaru BRZ tS', 'Bọc vô lăng Vertex 330mm cho Toyota GR86',
            'Gương chiếu hậu carbon bên trái cho Audi R8 Performance', 'Gương chiếu hậu carbon bên phải cho BMW M4 Competition', 'Gương chiếu hậu M Performance cho BMW M2 CS', 'Gương chiếu hậu AMG Carbon cho Mercedes AMG GT Black Series', 'Gương chiếu hậu Mugen Power cho Honda Civic Type R'
        ];
        $files = collect(Storage::disk('public')->allFiles('default/product'))
            ->filter(function ($file) {
                return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
            })->values()->all();
        
        foreach ($products as $productName) {
            $product = Product::create([
                'name' => $productName . ' ' . $faker->bothify('##??'),
                'description' => $faker->paragraphs(2, true),
                'original_price' => $faker->numberBetween(100000, 5000000),
                'quantity' => $faker->numberBetween(0, 1000),
                'sold' => $faker->numberBetween(0, 1000),
                'dimensions' => $faker->randomElement([
                    '10x5x5 cm', '20x15x10 cm', '30x20x10 cm', '40x25x15 cm'
                ]),
                'weight' => $faker->randomElement([
                    '0.2kg', '0.5kg', '1kg', '2.5kg', '5kg'
                ]),
                'color' => $faker->randomElement([
                    'Đen', 'Bạc', 'Xám', 'Trắng', 'Đỏ', 'Xanh dương'
                ]),
                'material' => $faker->randomElement([
                    'Nhôm', 'Thép không gỉ', 'Cao su', 'Nhựa ABS', 'Sắt hợp kim'
                ]),
                'compatibility' => $faker->randomElement([
                    'Toyota', 'Honda', 'Ford', 'Hyundai', 'Mazda', 'Kia', 'Nissan', 'Suzuki'
                ]),

                'promotion_id' => $faker->optional()->randomElement($promotionIds),
                'unit_id' => $faker->optional()->randomElement($unitIds),
                'supplier_id' => $faker->optional()->randomElement($supplierIds),
            ]);

            $product->categories()->attach($faker->randomElements($categoryIds, min(5, count($categoryIds))));

            Image::create([
                'product_id' => $product->id,
                'filename' => 'thumbnail_' . Str::random(5),
                'path' => '/storage/' . $faker->randomElement($files),
                'is_thumbnail' => true,
                'size' => rand(100000, 800000),
                'mime_type' => 'image/jpeg',
            ]);

            $images = [];
            for ($j = 0; $j < 9; $j++) {
                $images[] = [
                    'product_id' => $product->id,
                    'filename' => 'product_image_' . Str::random(5),
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
