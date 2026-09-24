<?php

namespace Database\Seeders\User;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator = Role::firstOrCreate([
            'name' => 'Administrator',
            'guard_name' => 'web',
        ]);

        $administrator->syncPermissions(
            Permission::all()
        );

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $manager->syncPermissions([
            'dashboard.view',

            'products.view',
            'products.create',
            'products.edit',

            'product-variants.view',
            'product-variants.create',
            'product-variants.edit',

            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.post',

            'stock-transfers.view',
            'stock-transfers.create',
            'stock-transfers.edit',
            'stock-transfers.post',

            'stock-issues.view',
            'stock-issues.create',
            'stock-issues.edit',
            'stock-issues.post',
            'stock-issues.reject',

            'stock-opnames.view',
            'stock-opnames.create',
            'stock-opnames.edit',
            'stock-opnames.post',

            'accounting.view',

            'purchasing.view',
            'purchasing.create',
            'purchasing.edit',
            'purchasing.post',

            'pos.access',
            'pos.open-session',
            'pos.sell',
            'pos.void',
            'pos.discount',
            'pos.close-session',

            'reports.view',
            'reports.export',

            'user-branches.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */

        $accounting = Role::firstOrCreate([
            'name' => 'Accounting',
            'guard_name' => 'web',
        ]);

        $accounting->syncPermissions([
            'dashboard.view',

            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
            'accounting.post',

            'reports.view',
            'reports.export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Warehouse
        |--------------------------------------------------------------------------
        */

        $warehouse = Role::firstOrCreate([
            'name' => 'Warehouse',
            'guard_name' => 'web',
        ]);

        $warehouse->syncPermissions([
            'dashboard.view',

            'products.view',

            'product-variants.view',

            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.post',

            'stock-transfers.view',
            'stock-transfers.create',
            'stock-transfers.edit',
            'stock-transfers.post',

            'stock-issues.view',
            'stock-issues.create',
            'stock-issues.edit',
            'stock-issues.post',
            'stock-issues.reject',

            'stock-opnames.view',
            'stock-opnames.create',
            'stock-opnames.edit',
            'stock-opnames.post',

            'reports.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cashier
        |--------------------------------------------------------------------------
        */

        $cashier = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);

        $cashier->syncPermissions([
            'dashboard.view',

            'products.view',
            'product-variants.view',

            'pos.access',
            'pos.open-session',
            'pos.sell',
            'pos.void',
            'pos.discount',
            'pos.close-session',
        ]);
    }
}