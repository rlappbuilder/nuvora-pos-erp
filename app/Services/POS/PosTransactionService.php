<?php

namespace App\Services\POS;

use App\Models\MasterData\Branch;
use App\Models\POS\CashierSession;
use App\Models\POS\PosSale;
use App\Models\MasterData\ProductVariantPrice;
use App\Models\Inventory\ProductStock;
use App\Services\Core\CodeGeneratorService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Services\Accounting\JournalEntryService;
class PosTransactionService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected InventoryService $inventoryService;

    protected JournalEntryService $journalEntryService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        InventoryService $inventoryService,
          JournalEntryService $journalEntryService
    ) {
        $this->codeGeneratorService = $codeGeneratorService;
        $this->inventoryService = $inventoryService;
        $this->journalEntryService = $journalEntryService;
    }

    public function create(array $data, $user): PosSale
    {
      //  dd('SERVICE MASUK', $data, $user->id);
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
            $this->createAccountingJournal(
                $sale,
                $session,
                $preparedDetails,
                $data['payments'],
                $user
            );
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

    protected function createAccountingJournal(
    PosSale $sale,
    CashierSession $session,
    array $preparedDetails,
    array $payments,
    $user
): void {
    /*
    |--------------------------------------------------------------------------
    | Accounting Context
    |--------------------------------------------------------------------------
    */

    $companyId = (int) $sale->company_id;
    $branchId = (int) $sale->branch_id;
    $entryDate = $sale->sale_date;

    /*
    |--------------------------------------------------------------------------
    | Resolve Accounting Journal
    |--------------------------------------------------------------------------
    */

    $journal = AccountingJournal::query()
        ->where('company_id', $companyId)
        ->where('code', 'CSH')
        ->where('is_active', true)
        ->first();

    if (!$journal) {
        throw ValidationException::withMessages([
            'accounting' => 'Cash Journal (CSH) tidak ditemukan atau tidak aktif.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Fiscal Year
    |--------------------------------------------------------------------------
    */

    $fiscalYear = FiscalYear::query()
        ->byCompany($companyId)
        ->open()
        ->whereDate('start_date', '<=', $entryDate)
        ->whereDate('end_date', '>=', $entryDate)
        ->first();

    if (!$fiscalYear) {
        throw ValidationException::withMessages([
            'accounting' => 'Fiscal year terbuka tidak ditemukan untuk tanggal transaksi.',
        ]);
    }

    $period = AccountingPeriod::query()
        ->byCompany($companyId)
        ->where('fiscal_year_id', $fiscalYear->id)
        ->open()
        ->forDate($entryDate)
        ->first();

    if (!$period) {
        throw ValidationException::withMessages([
            'accounting' => 'Accounting period terbuka tidak ditemukan untuk tanggal transaksi.',
        ]);
    }
    

    /*
    |--------------------------------------------------------------------------
    | Resolve Account Mapping
    |--------------------------------------------------------------------------
    */

       $getMappedAccount = function (string $key) use ($companyId): ChartOfAccount {
   
        $mapping = AccountMapping::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->active()
            ->first();

        if (!$mapping || !$mapping->account) {
            throw ValidationException::withMessages([
                'accounting' => "Account mapping [{$key}] belum tersedia.",
            ]);
        }

        $account = $mapping->account;

        if (
            !$account->status ||
            !$account->is_posting
        ) {
            throw ValidationException::withMessages([
                'accounting' => "Account mapping [{$key}] tidak menunjuk ke posting account yang aktif.",
            ]);
        }

        return $account;
    };

    /*
    |--------------------------------------------------------------------------
    | Revenue / COGS / Inventory Accounts
    |--------------------------------------------------------------------------
    */

    $salesAccount = $getMappedAccount(
        'settlement_revenue'
    );

    $cogsAccount = $getMappedAccount(
        'settlement_cogs'
    );

    $inventoryAccount = $getMappedAccount(
        'inventory_merchandise'
    );

    /*
    |--------------------------------------------------------------------------
    | Journal Lines
    |--------------------------------------------------------------------------
    */

    $lines = [];

    /*
    |--------------------------------------------------------------------------
    | Payment Accounts
    |--------------------------------------------------------------------------
    */

    $remainingPayment = (float) $sale->grand_total;

    foreach ($payments as $payment) {

        if ($remainingPayment <= 0) {
            break;
        }

        $paymentAmount = min(
            (float) $payment['amount'],
            $remainingPayment
        );

        if ($paymentAmount <= 0) {
            continue;
        }

        $paymentMethod = $payment['payment_method'];

        /*
        |--------------------------------------------------------------------------
        | Resolve Payment Account
        |--------------------------------------------------------------------------
        */

        if ($paymentMethod === 'cash') {

            $paymentAccount = ChartOfAccount::query()
                ->whereKey($session->cash_account_id)
                ->where('company_id', $companyId)
                ->active()
                ->posting()
                ->first();

            if (!$paymentAccount) {
                throw ValidationException::withMessages([
                    'accounting' => 'Cash account pada cashier session tidak valid.',
                ]);
            }

        } else {

            $mappingKey = match ($paymentMethod) {
                'qris' => 'pos_qris',
                'debit_card' => 'pos_debit_card',
                'transfer' => 'pos_transfer',
                'e_wallet' => 'pos_e_wallet',

                default => null,
            };

            if (!$mappingKey) {
                throw ValidationException::withMessages([
                    'payments' => "Payment method [{$paymentMethod}] tidak memiliki account mapping.",
                ]);
            }

            $paymentAccount = $getMappedAccount(
                $mappingKey
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Debit Payment Account
        |--------------------------------------------------------------------------
        */

        $lines[] = [
            'account_id' => $paymentAccount->id,
            'description' => "POS Payment {$paymentMethod}",
            'debit' => $paymentAmount,
            'credit' => 0,
        ];

        $remainingPayment -= $paymentAmount;
    }

    /*
    |--------------------------------------------------------------------------
    | Credit Sales Revenue
    |--------------------------------------------------------------------------
    */

    $lines[] = [
        'account_id' => $salesAccount->id,
        'description' => 'POS Merchandise Sales',
        'debit' => 0,
        'credit' => (float) $sale->grand_total,
    ];

    /*
    |--------------------------------------------------------------------------
    | Calculate Total COGS
    |--------------------------------------------------------------------------
    */

    $totalCost = collect($preparedDetails)
        ->sum(fn ($detail) => (float) $detail['total_cost']);

    /*
    |--------------------------------------------------------------------------
    | COGS / Inventory
    |--------------------------------------------------------------------------
    */

    if ($totalCost > 0) {

        $lines[] = [
            'account_id' => $cogsAccount->id,
            'description' => 'POS Merchandise Cost of Goods Sold',
            'debit' => $totalCost,
            'credit' => 0,
        ];

        $lines[] = [
            'account_id' => $inventoryAccount->id,
            'description' => 'POS Merchandise Inventory',
            'debit' => 0,
            'credit' => $totalCost,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create Journal Entry
    |--------------------------------------------------------------------------
    */

    $journalEntry = $this->journalEntryService->create([
        'company_id' => $companyId,
        'branch_id' => $branchId,
        'accounting_journal_id' => $journal->id,
        'fiscal_year_id' => $fiscalYear->id,
        'accounting_period_id' => $period->id,
        'entry_date' => $entryDate->toDateString(),
        'reference' => $sale->sale_number,
        'description' => "POS Sale {$sale->sale_number}",
        'lines' => $lines,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Post Journal
    |--------------------------------------------------------------------------
    */

    $this->journalEntryService->post(
        $journalEntry
    );
}
}