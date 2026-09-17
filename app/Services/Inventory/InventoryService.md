public function stockOut(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = ProductStock::where([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'product_variant_id' => $data['product_variant_id'],
            'unit_id' => $data['unit_id'],
             'reseller_id' =>$data['reseller_id'] ?? null,
        ])
        ->lockForUpdate()
        ->firstOrFail();

        if ($stock->available_qty < $data['qty']) {
            throw new \RuntimeException(
                'Insufficient available stock.'
            );
        }

        $stock->on_hand_qty -= $data['qty'];

        $stock->available_qty =
            $stock->on_hand_qty -
            $stock->reserved_qty;

        $stock->last_transaction_at =
            $data['transaction_date'];

        $stock->updated_by =
            auth()->id();

        $stock->save();

        $this->createMovement(
            $stock,
            [
                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' => 0,

                'qty_out' =>
                    $data['qty'],

                'unit_cost' =>
                    $data['unit_cost']
                    ?? $stock->average_cost,

                'total_cost' =>
                    $data['total_cost']
                    ??
                    (
                        $data['qty']
                        *
                        ($data['unit_cost']
                            ?? $stock->average_cost)
                    ),

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,

                'reseller_id' =>
                $data['reseller_id'] ?? null,
            ]
        );

    });
}
public function stockIn(array $data): void
{
    DB::transaction(function () use ($data) {

        $stock = $this->updateCurrentStock([

            'company_id' =>
                $data['company_id'],

            'branch_id' =>
                $data['branch_id'],

            'warehouse_id' =>
                $data['warehouse_id'],

            'product_variant_id' =>
                $data['product_variant_id'],

            'unit_id' =>
                $data['unit_id'],

            'qty' =>
                $data['qty'],

            'average_cost' =>
                $data['unit_cost'] ?? null,

             'update_average_cost' =>
             true,

            'transaction_date' =>
                $data['transaction_date'],

            'reseller_id' =>
            $data['reseller_id'] ?? null,

        ]);

        $this->createMovement(

            $stock,

            [

                'reference_type' =>
                    $data['reference_type'],

                'reference_id' =>
                    $data['reference_id'],

                'reference_number' =>
                    $data['reference_number'],

                'qty_in' =>
                    $data['qty'],

                'qty_out' =>
                    0,

                'unit_cost' =>
                    $data['unit_cost'] ?? 0,

                'total_cost' =>
                    $data['total_cost']
                    ??
                    (
                        $data['qty']
                        *
                        ($data['unit_cost'] ?? 0)
                    ),

                'transaction_date' =>
                    $data['transaction_date'],

                'description' =>
                    $data['description'] ?? null,
                
                 'reseller_id' =>
                $data['reseller_id'] ?? null,

            ]

        );

    });
} /*
    |--------------------------------------------------------------------------
    | Private Methods
    |--------------------------------------------------------------------------
    */

    private function updateCurrentStock(
    array $data
): ProductStock {

    /*
    |--------------------------------------------------------------------------
    | Find / Lock Stock
    |--------------------------------------------------------------------------
    */

    $query = ProductStock::query()
        ->where(
            'company_id',
        $data['company_id'] ?? null
        )
        ->where(
            'branch_id',
            $data['branch_id']
        )
        ->where(
            'warehouse_id',
            $data['warehouse_id']
        )
        ->where(
            'product_variant_id',
            $data['product_variant_id']
        )
        ->where(
            'unit_id',
            $data['unit_id']
        )
        ->where(
            'reseller_id',
            $data['reseller_id'] ?? null
        );

    if (
        ($data['lock'] ?? false)
        === true
    ) {

        $query->lockForUpdate();

    }


    $stock = $query->first();


    /*
    |--------------------------------------------------------------------------
    | Create New Stock
    |--------------------------------------------------------------------------
    */

    if (! $stock) {

        $stock = new ProductStock();

        $stock->company_id =
        $data['company_id'] ?? null;

        $stock->branch_id =
            $data['branch_id'];

        $stock->warehouse_id =
            $data['warehouse_id'];

        $stock->product_variant_id =
            $data['product_variant_id'];

        $stock->reseller_id =
        $data['reseller_id'] ?? null;

        $stock->unit_id =
            $data['unit_id'];

        $stock->on_hand_qty =
            0;

        $stock->reserved_qty =
            0;

        $stock->available_qty =
            0;

        $stock->average_cost =
            0;

    }


    /*
    |--------------------------------------------------------------------------
    | Existing Stock Snapshot
    |--------------------------------------------------------------------------
    */

    $oldQty =
        (float) $stock->on_hand_qty;

    $oldAverageCost =
        (float) $stock->average_cost;

    $movementQty =
        (float) $data['qty'];


    /*
    |--------------------------------------------------------------------------
    | Update Quantity
    |--------------------------------------------------------------------------
    */

    $stock->on_hand_qty =
        $oldQty +
        $movementQty;


    /*
    |--------------------------------------------------------------------------
    | Prevent Negative Stock
    |--------------------------------------------------------------------------
    */

    if (
        $stock->on_hand_qty < 0
    ) {

        throw new \RuntimeException(
            'Stock quantity cannot be negative.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Weighted Average
    |--------------------------------------------------------------------------
    */

    if (
        ($data['update_average_cost'] ?? false)
        === true
        &&
        $movementQty > 0
    ) {

        $incomingCost =
            (float) (
                $data['average_cost']
                ?? 0
            );


        if (
            $oldQty <= 0
        ) {

            $stock->average_cost =
                $incomingCost;

        } else {

            $stock->average_cost =
                (
                    (
                        $oldQty *
                        $oldAverageCost
                    )
                    +
                    (
                        $movementQty *
                        $incomingCost
                    )
                )
                /
                $stock->on_hand_qty;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Available Quantity
    |--------------------------------------------------------------------------
    */

    $stock->available_qty =
        $stock->on_hand_qty -
        $stock->reserved_qty;


    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    $stock->last_transaction_at =
        $data['transaction_date'];


    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    $stock->updated_by =
        auth()->id();

    $stock->created_by ??=
        auth()->id();


    $stock->save();


    return $stock;
}private function createMovement(ProductStock $stock,array $data): InventoryMovement
{
    return InventoryMovement::create([

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'company_id'         => $stock->company_id,

        'branch_id'          => $stock->branch_id,

        'warehouse_id'       => $stock->warehouse_id,

        'reseller_id' => $data['reseller_id'] ?? null,

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        'product_variant_id' => $stock->product_variant_id,

        'unit_id'            => $stock->unit_id,

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        'reference_type'     => $data['reference_type'],

        'reference_id'       => $data['reference_id'],

        'reference_number'   => $data['reference_number'],

        /*
        |--------------------------------------------------------------------------
        | Movement
        |--------------------------------------------------------------------------
        */

        'qty_in'             => $data['qty_in'] ?? 0,

        'qty_out'            => $data['qty_out'] ?? 0,

        'balance_qty'        => $stock->on_hand_qty,

        /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */

        'unit_cost'          => $data['unit_cost'] ?? 0,

        'total_cost'         => $data['total_cost'] ?? 0,

        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'transaction_date'   => $data['transaction_date'],

        'description'        => $data['description'] ?? null,

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by'         => auth()->id(),

    ]);
}