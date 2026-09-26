<?php

namespace App\Services\POS;

use App\Models\MasterData\Branch;
use App\Models\POS\CashierSession;
use App\Models\POS\PosSale;
use App\Models\Product\ProductVariantPrice;
use App\Models\Inventory\ProductStock;
use App\Services\Core\CodeGeneratorService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosTransactionService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected InventoryService $inventoryService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        InventoryService $inventoryService
    ) {
        $this->codeGeneratorService = $codeGeneratorService;
        $this->inventoryService = $inventoryService;
    }

    public function create(array $data, $user): PosSale
    {
        return DB::transaction(function () use ($data, $user) {

            $currentBranchId = session('current_branch_id');

            if (!$currentBranchId) {
                throw ValidationException::withMessages([
                    'branch' => 'Current branch belum dipilih.',
                ]);
            }

            $branch = Branch::query()
                ->whereKey($currentBranchId)
                ->where('company_id', $user->company_id)
                ->lockForUpdate()
                ->first();

            if (!$branch) {
                throw ValidationException::withMessages([
                    'branch' => 'Current branch tidak valid.',
                ]);
            }

            $session = CashierSession::query()
                ->where('company_id', $user->company_id)
                ->where('branch_id', $branch->id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$session) {
                throw ValidationException::withMessages([
                    'session' => 'Tidak ada cashier session yang sedang terbuka.',
                ]);
            }

            $subtotal = 0;

            $preparedDetails = [];

            foreach ($data['details'] as $index => $detail) {

                $price = ProductVariantPrice::query()
                    ->where('branch_id', $branch->id)
                    ->where('product_variant_id', $detail['product_variant_id'])
                    ->where('unit_id', $detail['unit_id'])
                    ->where('price_type_id', $detail['price_type_id'])
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('effective_from')
                            ->orWhereDate('effective_from', '<=', now()->toDateString());
                    })
                    ->where(function ($query) {
                        $query->whereNull('effective_until')
                            ->orWhereDate('effective_until', '>=', now()->toDateString());
                    })
                    ->orderByDesc('effective_from')
                    ->lockForUpdate()
                    ->first();

                if (!$price) {
                    throw ValidationException::withMessages([
                        "details.$index" => 'Harga produk tidak ditemukan untuk branch, unit, dan price type yang dipilih.',
                    ]);
                }

                $qty = (float) $detail['qty'];
                $unitPrice = (float) $price->selling_price;
                $discount = (float) ($detail['discount_amount'] ?? 0);

                $lineSubtotal = max(
                    0,
                    ($qty * $unitPrice) - $discount
                );

                $stock = ProductStock::query()
                    ->where('company_id', $user->company_id)
                    ->where('branch_id', $branch->id)
                    ->where('warehouse_id', $session->warehouse_id)
                    ->where('product_variant_id', $detail['product_variant_id'])
                    ->where('unit_id', $detail['unit_id'])
                    ->whereNull('reseller_id')
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw ValidationException::withMessages([
                        "details.$index" => 'Stock produk tidak ditemukan di warehouse cashier.',
                    ]);
                }

                if ((float) $stock->available_qty < $qty) {
                    throw ValidationException::withMessages([
                        "details.$index" => 'Stock tidak mencukupi.',
                    ]);
                }

                $unitCost = (float) $stock->average_cost;
                $totalCost = $qty * $unitCost;

                $preparedDetails[] = [
                    'product_variant_id' => $detail['product_variant_id'],
                    'unit_id' => $detail['unit_id'],
                    'price_type_id' => $detail['price_type_id'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'subtotal' => $lineSubtotal,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];

                $subtotal += $lineSubtotal;
            }

            $headerDiscount = (float) ($data['discount_amount'] ?? 0);

            if ($headerDiscount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount tidak boleh melebihi subtotal.',
                ]);
            }

            $grandTotal = $subtotal - $headerDiscount;

            $paidAmount = collect($data['payments'])
                ->sum(fn ($payment) => (float) $payment['amount']);

            if ($paidAmount < $grandTotal) {
                throw ValidationException::withMessages([
                    'payments' => 'Total payment belum mencukupi grand total.',
                ]);
            }

            $changeAmount = $paidAmount - $grandTotal;

            $saleNumber = $this->codeGeneratorService
                ->next('pos_sale');

            $sale = PosSale::create([
                'company_id' => $user->company_id,
                'branch_id' => $branch->id,
                'warehouse_id' => $session->warehouse_id,
                'cashier_session_id' => $session->id,
                'customer_id' => $data['customer_id'] ?? null,
                'sale_number' => $saleNumber,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $headerDiscount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => 'posted',
                'note' => $data['note'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($preparedDetails as $detail) {

                $saleDetail = $sale->details()->create($detail);

                $this->inventoryService->stockOut([
                    'company_id' => $user->company_id,
                    'branch_id' => $branch->id,
                    'warehouse_id' => $session->warehouse_id,
                    'product_variant_id' => $detail['product_variant_id'],
                    'unit_id' => $detail['unit_id'],
                    'reseller_id' => null,
                    'qty' => $detail['qty'],
                    'unit_cost' => $detail['unit_cost'],
                    'total_cost' => $detail['total_cost'],
                    'transaction_date' => now(),
                    'reference_type' => PosSale::class,
                    'reference_id' => $sale->id,
                    'reference_number' => $sale->sale_number,
                    'description' => 'POS Sale',
                ]);
            }

            foreach ($data['payments'] as $payment) {
                $sale->payments()->create([
                    'payment_method' => $payment['payment_method'],
                    'amount' => $payment['amount'],
                    'reference_no' => $payment['reference_no'] ?? null,
                    'note' => $payment['note'] ?? null,
                ]);
            }

            return $sale->fresh([
                'company',
                'branch',
                'warehouse',
                'cashierSession',
                'customer',
                'details.productVariant.product',
                'details.unit',
                'details.priceType',
                'payments',
            ]);
        });
    }

    public function getActiveSession($user): ?CashierSession
    {
        $branchId = session('current_branch_id');

        if (!$branchId) {
            return null;
        }

        return CashierSession::query()
            ->with([
                'branch',
                'warehouse',
                'cashAccount',
            ])
            ->where('company_id', $user->company_id)
            ->where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
    }
}