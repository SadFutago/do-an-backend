<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'nhân viên kho']);
        Role::create(['name' => 'nhân viên bán hàng']);
        Role::create(['name' => 'admin']);

        $employeeRoles = Role::whereIn('name', ['nhân viên kho', 'nhân viên bán hàng'])->get();
        $viewPermissions = Permission::where('name', 'like', '%.view')->pluck('id')->toArray();
        $rolePermission = [];

        foreach ($employeeRoles as $role) {
            foreach ($viewPermissions as $permissionId) {
                $rolePermission[] = [
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                ];
            }
        }

        $warehouseRole = Role::where('name', 'nhân viên kho')->first();
        $warehouseManageModules = ['cancels', 'checks', 'disposals', 'imports', 'exports', 'inventories', 'locations', 'units'];
        $warehouseManagePermissions = Permission::whereIn('name', array_map(fn($module) => "$module.manage", $warehouseManageModules))->pluck('id')->toArray();
        
        foreach ($warehouseManagePermissions as $permissionId) {
            $rolePermission[] = [
                'role_id' => $warehouseRole->id,
                'permission_id' => $permissionId,
            ];
        }

        $salesRole = Role::where('name', 'nhân viên bán hàng')->first();
        $salesManageModules = ['products', 'articles', 'categories', 'orders', 'vouchers', 'promotions'];
        $salesManagePermissions = Permission::whereIn('name', array_map(fn($module) => "$module.manage", $salesManageModules))->pluck('id')->toArray();
        
        foreach ($salesManagePermissions as $permissionId) {
            $rolePermission[] = [
                'role_id' => $salesRole->id,
                'permission_id' => $permissionId,
            ];
        }

        DB::table('role_permission')->insert($rolePermission);
    }
}
