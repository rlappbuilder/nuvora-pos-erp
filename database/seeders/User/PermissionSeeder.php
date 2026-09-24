<?php

namespace Database\Seeders\User;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            'dashboard.view',

            /*
            |--------------------------------------------------------------------------
            | Master Data - Products
            |--------------------------------------------------------------------------
            */

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            /*
            |--------------------------------------------------------------------------
            | Product Variants
            |--------------------------------------------------------------------------
            */

            'product-variants.view',
            'product-variants.create',
            'product-variants.edit',
            'product-variants.delete',

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.post',

            /*
            |--------------------------------------------------------------------------
            | Stock Transfer
            |--------------------------------------------------------------------------
            */

            'stock-transfers.view',
            'stock-transfers.create',
            'stock-transfers.edit',
            'stock-transfers.delete',
            'stock-transfers.post',

            /*
            |--------------------------------------------------------------------------
            | Stock Issue
            |--------------------------------------------------------------------------
            */

            'stock-issues.view',
            'stock-issues.create',
            'stock-issues.edit',
            'stock-issues.delete',
            'stock-issues.post',
            'stock-issues.reject',

            /*
            |--------------------------------------------------------------------------
            | Stock Opname
            |--------------------------------------------------------------------------
            */

            'stock-opnames.view',
            'stock-opnames.create',
            'stock-opnames.edit',
            'stock-opnames.delete',
            'stock-opnames.post',

            /*
            |--------------------------------------------------------------------------
            | Accounting
            |--------------------------------------------------------------------------
            */

            'accounting.view',
            'accounting.create',
            'accounting.edit',
            'accounting.delete',
            'accounting.post',

            /*
            |--------------------------------------------------------------------------
            | Purchasing
            |--------------------------------------------------------------------------
            */

            'purchasing.view',
            'purchasing.create',
            'purchasing.edit',
            'purchasing.delete',
            'purchasing.post',

            /*
            |--------------------------------------------------------------------------
            | POS
            |--------------------------------------------------------------------------
            */

            'pos.access',
            'pos.open-session',
            'pos.sell',
            'pos.void',
            'pos.discount',
            'pos.close-session',

            /*
            |--------------------------------------------------------------------------
            | User Management
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            /*
            |--------------------------------------------------------------------------
            | Role & Permission
            |--------------------------------------------------------------------------
            */

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            /*
            |--------------------------------------------------------------------------
            | Branch Access
            |--------------------------------------------------------------------------
            */

            'user-branches.view',
            'user-branches.manage',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            'reports.view',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}