<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\Inventory\OpeningStockHeader;
use App\Models\Inventory\OpeningStockDetail;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\InventoryMovement;
use App\Models\User;
class OpeningStockTest extends TestCase
{
    public function test_opening_stock_can_be_created(): void
    {
        $user = User::first();

        $this->actingAs($user);
        
        $company = Company::first();

        $branch = Branch::first();

        $warehouse = Warehouse::first();

        $variant = ProductVariant::first();

        $unit = Unit::first();

        $response = $this->post(
            '/inventory/opening-stocks',
            [
                'company_id' => $company->id,

                'branch_id' => $branch->id,

                'warehouse_id' => $warehouse->id,

                'transaction_date' => now()->toDateString(),

                'description' => 'Test Opening Stock',

                'details' => [

                    [
                        'product_variant_id' => $variant->id,

                        'unit_id' => $unit->id,

                        'qty' => 10,

                        'unit_cost' => 50000,

                        'description' => 'Test',

                    ],

                ],

            ]
        );

        //$response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $response->assertRedirect();
        $this->assertDatabaseCount(
            'opening_stock_headers',
            1
        );
        $this->assertDatabaseHas(
            'opening_stock_headers',
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'status' => 'Posted',
            ]
        );

        $header = OpeningStockHeader::latest()->first();

        $this->assertDatabaseHas(
            'opening_stock_details',
            [
                'opening_stock_header_id' => $header->id,
                'product_variant_id' => $variant->id,
                'unit_id' => $unit->id,
                'qty' => 10,
            ]
        );

        $this->assertDatabaseHas(
            'product_stocks',
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'unit_id' => $unit->id,
                'on_hand_qty' => 10,
            ]
        );

        $this->assertDatabaseHas(
            'inventory_movements',
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'unit_id' => $unit->id,
                'reference_type' => 'OPENING_STOCK',
                'qty_in' => 10,
                'qty_out' => 0,
            ]
        );
    }
}