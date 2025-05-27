<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deliverer')->nullable();        // ten nguoi giao hang
            $table->string('address')->nullable();      // dia chi giao hang
            $table->date('date');
            $table->unsignedInteger('total_amount')->default(0);        // tong tien hang
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('import_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');        // so luong
            // $table->unsignedInteger('actual_quantity')->nullable();     // thuc nhap
            $table->unsignedInteger('price');       // don gia
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');     // so lo
            $table->unsignedInteger('quantity')->default(0);
            $table->date('manufacture_date');
            $table->date('expiry_date');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_id', 'batch_number']);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('zone', 10)->nullable();     // khu
            $table->string('aisle', 5)->nullable();     // lối đi
            $table->string('rack', 5)->nullable();     // kệ
            $table->string('shelf', 5)->nullable();     // tầng kệ
            $table->string('bin', 5)->nullable();     // ngăn
            $table->tinyInteger('status')->default(0)->comment('0: empty, 1: full, 2: locked');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();        // hang moi, hang thanh ly, ...
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_location', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedInteger('quantity')->default(0);
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->primary(['inventory_id', 'location_id']);
        });

        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('receiver')->nullable();     // ten nguoi nhan hang
            $table->string('address')->nullable();      // dia chi (bo phan)
            $table->string('reason')->nullable();       // ly do xuat
            $table->unsignedInteger('total_amount')->default(0);        // tong tien hang
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('export_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');                        // so luong
            // $table->unsignedInteger('actual_quantity')->nullable();     // thuc xuat
            $table->unsignedInteger('price');                           // don gia
            $table->timestamps();
        });
        // phiếu thanh lý
        Schema::create('disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->unsignedInteger('total_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('disposal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            // $table->unsignedInteger('actual_quantity')->nullable();
            $table->unsignedInteger('price');
            $table->string('method')->nullable();
            $table->timestamps();
        });
        // phiếu kiểm kê
        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->unsignedInteger('total_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('check_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('quality', ['Còn tốt 100%', 'Kém phẩm chất', 'Mất phẩm chất'])->nullable();
            $table->unsignedInteger('quantity');                        // so luong theo so sach
            // $table->unsignedInteger('actual_quantity')->nullable();                 // so luong theo kiem ke
            $table->unsignedInteger('price');                           // don gia
            $table->timestamps();
        });
        // phiếu hủy hàng
        Schema::create('cancels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->unsignedInteger('total_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('cancel_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price');
            $table->string('method')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancel_details');
        Schema::dropIfExists('cancels');
        Schema::dropIfExists('check_details');
        Schema::dropIfExists('checks');
        Schema::dropIfExists('disposal_details');
        Schema::dropIfExists('disposals');
        Schema::dropIfExists('export_details');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('inventory_location');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('import_details');
        Schema::dropIfExists('imports');
    }
};
