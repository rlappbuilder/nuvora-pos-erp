<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockOpnameHeader;
use App\Models\Inventory\StockOpnameDetail;
use App\Models\Inventory\ProductStock;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create Stock Opname
    |--------------------------------------------------------------------------
    */

    public function create(array $data): StockOpnameHeader
    {
        return DB::transaction(function () use ($data) {

            $header = StockOpnameHeader::create([

                'company_id' =>
                    $data['company_id'],

                'branch_id' =>
                    $data['branch_id'],

                'warehouse_id' =>
                    $data['warehouse_id'],

                'number' =>
                    $this->codeGeneratorService
                        ->next('stock_opname'),

                'transaction_date' =>
                    $data['transaction_date'],

                'status' =>
                    'Draft',

                'description' =>
                    $data['description'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);

            foreach ($data['details'] as $detail) {

                $stock = ProductStock::where([
                    'company_id' =>
                        $data['company_id'],

                    'branch_id' =>
                        $data['branch_id'],

                    'warehouse_id' =>
                        $data['warehouse_id'],

                    'product_variant_id' =>
                        $detail['product_variant_id'],

                    'unit_id' =>
                        $detail['unit_id'],
                ])->first();

                $systemQty =
                    $stock?->on_hand_qty ?? 0;

                $unitCost =
                    $stock?->average_cost ?? 0;

                $actualQty =
                    $detail['actual_qty'];

                $differenceQty =
                    $actualQty - $systemQty;

                $differenceCost =
                    $differenceQty * $unitCost;

                StockOpnameDetail::create([

                    'stock_opname_header_id' =>
                        $header->id,

                    'product_variant_id' =>
                        $detail['product_variant_id'],

                    'unit_id' =>
                        $detail['unit_id'],

                    'system_qty' =>
                        $systemQty,

                    'actual_qty' =>
                        $actualQty,

                    'difference_qty' =>
                        $differenceQty,

                    'unit_cost' =>
                        $unitCost,

                    'difference_cost' =>
                        $differenceCost,

                    'description' =>
                        $detail['description'] ?? null,

                    'created_by' =>
                        auth()->id(),

                ]);
            }

            return $header;
        });
    }
}