<?php

namespace App\Services\POS;

use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\POS\CashierSession;
use App\Models\User;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Accounting\BranchAccountMapping;

class CashierSessionService
{
    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Open Session
    |--------------------------------------------------------------------------
    */

    public function open(
        User $user,
        int $warehouseId,
        //int $cashAccountId,
        float $openingBalance
    ): CashierSession {
        return DB::transaction(function () use (
            $user,
            $warehouseId,
          //  $cashAccountId,
            $openingBalance
        ) {

            /*
            |--------------------------------------------------------------------------
            | Current Branch
            |--------------------------------------------------------------------------
            */

            $currentBranchId =
                session('current_branch_id');

            if (! $currentBranchId) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch belum dipilih.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Branch
            |--------------------------------------------------------------------------
            */

            $branch = Branch::query()
                ->whereKey($currentBranchId)
                ->lockForUpdate()
                ->first();

            if (! $branch) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch tidak ditemukan.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Company Validation
            |--------------------------------------------------------------------------
            */

            if (
                (int) $branch->company_id !==
                (int) $user->company_id
            ) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch tidak sesuai dengan company user.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | User Branch Access
            |--------------------------------------------------------------------------
            */

            $hasBranchAccess =
                $user->branches()
                    ->where(
                        'branches.id',
                        $branch->id
                    )
                    ->exists();

            if (! $hasBranchAccess) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'User tidak memiliki akses ke current branch.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */

            $warehouse = Warehouse::query()
                ->whereKey($warehouseId)
                ->where(
                    'branch_id',
                    $branch->id
                )
                ->where(
                    'status',
                    true
                )
                ->lockForUpdate()
                ->first();

            if (! $warehouse) {
                throw ValidationException::withMessages([
                    'warehouse_id' =>
                        'Warehouse tidak valid untuk current branch.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Open Session
            |--------------------------------------------------------------------------
            */

            $existingSession =
                CashierSession::query()
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where(
                        'branch_id',
                        $branch->id
                    )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'status',
                        'open'
                    )
                    ->lockForUpdate()
                    ->first();

            if ($existingSession) {
                throw ValidationException::withMessages([
                    'session' =>
                        'Cashier masih memiliki session yang sedang terbuka.',
                ]);
            }

           /*
            |--------------------------------------------------------------------------
            | Branch Cashier Drawer
            |--------------------------------------------------------------------------
            */

            $cashAccountMapping =
                BranchAccountMapping::query()
                    ->with('account')
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where(
                        'branch_id',
                        $branch->id
                    )
                    ->where(
                        'key',
                        'cashier_drawer'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->lockForUpdate()
                    ->first();

            if (! $cashAccountMapping) {
                throw ValidationException::withMessages([
                    'cash_account_id' =>
                        'Cashier drawer belum dikonfigurasi untuk branch ini.',
                ]);
            }

            $cashAccount =
                $cashAccountMapping->account;

            if (
                ! $cashAccount ||
                (int) $cashAccount->company_id !==
                    (int) $user->company_id ||
                ! $cashAccount->status ||
                ! $cashAccount->is_posting
            ) {
                throw ValidationException::withMessages([
                    'cash_account_id' =>
                        'Cashier drawer tidak valid untuk branch ini.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Previous Session
            |--------------------------------------------------------------------------
            */

            $previousSession =
                CashierSession::query()
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where(
                        'branch_id',
                        $branch->id
                    )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'status',
                        'closed'
                    )
                    ->latest('closed_at')
                    ->lockForUpdate()
                    ->first();

            /*
            |--------------------------------------------------------------------------
            | Opening Balance
            |--------------------------------------------------------------------------
            |
            | If a previous session has carry-forward cash,
            | it becomes the opening balance automatically.
            |
            */

           if ($previousSession) {

                $carryForward =
                    $this->calculateCarryForward(
                        $previousSession
                    );

                if ($carryForward > 0) {
                    $openingBalance =
                        $carryForward;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Session Number
            |--------------------------------------------------------------------------
            */

            $sessionNumber =
                $this->codeGeneratorService
                    ->next('cashier_session');

            /*
            |--------------------------------------------------------------------------
            | Create Session
            |--------------------------------------------------------------------------
            */

            $session =
                CashierSession::create([
                    'company_id' =>
                        $user->company_id,

                    'branch_id' =>
                        $branch->id,

                    'warehouse_id' =>
                        $warehouse->id,

                    'user_id' =>
                        $user->id,

                    'cash_account_id' =>
                        $cashAccount->id,

                    'previous_session_id' =>
                        $previousSession?->id,

                    'session_number' =>
                        $sessionNumber,

                    'opened_at' =>
                        now(),

                    'opening_balance' =>
                        $openingBalance,

                    'status' =>
                        'open',
                ]);

            return $session->fresh([
                'company',
                'branch',
                'warehouse',
                'user',
                'cashAccount',
                'previousSession',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Carry Forward
    |--------------------------------------------------------------------------
    */

    protected function calculateCarryForward(
        CashierSession $session
    ): float {
        $closingBalance =
            (float) (
                $session->closing_balance ?? 0
            );

        $totalPostedDeposits =
            $session->deposits()
                ->where(
                    'status',
                    'posted'
                )
                ->sum('amount');

        return max(
            0,
            $closingBalance -
            (float) $totalPostedDeposits
        );
    }
/*
|--------------------------------------------------------------------------
| Get Opening Session Context
|--------------------------------------------------------------------------
*/

public function getOpeningContext(
    User $user
): array {

    $currentBranchId =
        session('current_branch_id');

    if (! $currentBranchId) {
        return [
            'previous_session' => null,
            'previous_closing_balance' => 0,
            'posted_deposits' => 0,
            'carry_forward' => 0,
        ];
    }

    $previousSession =
        CashierSession::query()
            ->with([
                'cashAccount',
                'warehouse',
            ])
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'status',
                'closed'
            )
            ->latest('closed_at')
            ->first();

    if (! $previousSession) {
        return [
            'previous_session' => null,
            'previous_closing_balance' => 0,
            'posted_deposits' => 0,
            'carry_forward' => 0,
        ];
    }

    $postedDeposits =
        (float) $previousSession
            ->deposits()
            ->where(
                'status',
                'posted'
            )
            ->sum('amount');

    $closingBalance =
        (float) (
            $previousSession->closing_balance ?? 0
        );

    $carryForward =
        max(
            0,
            $closingBalance - $postedDeposits
        );

    return [
        'previous_session' => [
            'id' =>
                $previousSession->id,

            'session_number' =>
                $previousSession->session_number,

            'closed_at' =>
                $previousSession->closed_at,

            'closing_balance' =>
                $closingBalance,

            'cash_account' =>
                $previousSession->cashAccount
                    ? [
                        'id' =>
                            $previousSession
                                ->cashAccount
                                ->id,

                        'code' =>
                            $previousSession
                                ->cashAccount
                                ->code,

                        'name' =>
                            $previousSession
                                ->cashAccount
                                ->name,
                    ]
                    : null,

            'warehouse' =>
                $previousSession->warehouse
                    ? [
                        'id' =>
                            $previousSession
                                ->warehouse
                                ->id,

                        'code' =>
                            $previousSession
                                ->warehouse
                                ->code,

                        'name' =>
                            $previousSession
                                ->warehouse
                                ->name,
                    ]
                    : null,
        ],

        'previous_closing_balance' =>
            $closingBalance,

        'posted_deposits' =>
            $postedDeposits,

        'carry_forward' =>
            $carryForward,
    ];
}
    /*
    |--------------------------------------------------------------------------
    | Get Active Session
    |--------------------------------------------------------------------------
    */

    public function getActiveSession(
        User $user
    ): ?CashierSession {

        $currentBranchId =
            session('current_branch_id');

        if (! $currentBranchId) {
            return null;
        }

        return CashierSession::query()
            ->with([
                'company',
                'branch',
                'warehouse',
                'user',
                'cashAccount',
                'previousSession',
            ])
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'status',
                'open'
            )
            ->first();
    }
/*
|--------------------------------------------------------------------------
| Get Close Session Summary
|--------------------------------------------------------------------------
*/

public function getCloseSummary(
    CashierSession $session
): array {
    $session->loadMissing([
        'branch',
        'warehouse',
        'user',
        'cashAccount',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Transaction Summary
    |--------------------------------------------------------------------------
    */

    $paymentSummary = DB::table('pos_sale_payments')
        ->join(
            'pos_sales',
            'pos_sales.id',
            '=',
            'pos_sale_payments.pos_sale_id'
        )
        ->where(
            'pos_sales.cashier_session_id',
            $session->id
        )
        ->where(
            'pos_sales.status',
            'posted'
        )
        ->select(
            'pos_sale_payments.payment_method',
            DB::raw('COUNT(DISTINCT pos_sale_payments.pos_sale_id) as transaction_count'),
            DB::raw('SUM(pos_sale_payments.amount) as amount')
        )
        ->groupBy(
            'pos_sale_payments.payment_method'
        )
        ->get()
        ->keyBy('payment_method');

    $getPayment = function (
        string $method
    ) use ($paymentSummary): array {
        $row = $paymentSummary->get($method);

        return [
            'transaction_count' =>
                (int) ($row->transaction_count ?? 0),

            'amount' =>
                (float) ($row->amount ?? 0),
        ];
    };

    $cash = $getPayment('cash');
    $qris = $getPayment('qris');
    $debitCard = $getPayment('debit_card');
    $eWallet = $getPayment('e_wallet');
    $transfer = $getPayment('transfer');

    $totalSales =
        $cash['amount']
        + $qris['amount']
        + $debitCard['amount']
        + $eWallet['amount']
        + $transfer['amount'];

    /*
    |--------------------------------------------------------------------------
    | Cash Deposit
    |--------------------------------------------------------------------------
    */

    $cashDeposit =
        (float) $session
            ->deposits()
            ->where('status', 'posted')
            ->sum('amount');

    /*
    |--------------------------------------------------------------------------
    | Cash Reconciliation
    |--------------------------------------------------------------------------
    */

    $openingCash =
        (float) (
            $session->opening_balance ?? 0
        );

    $expectedCash =
        $openingCash
        + $cash['amount']
        - $cashDeposit;

    return [
        'session' => [
            'id' =>
                $session->id,

            'session_number' =>
                $session->session_number,

            'opened_at' =>
                $session->opened_at,

            'branch' =>
                $session->branch
                    ? [
                        'id' =>
                            $session->branch->id,

                        'code' =>
                            $session->branch->code,

                        'name' =>
                            $session->branch->name,
                    ]
                    : null,

            'warehouse' =>
                $session->warehouse
                    ? [
                        'id' =>
                            $session->warehouse->id,

                        'code' =>
                            $session->warehouse->code,

                        'name' =>
                            $session->warehouse->name,
                    ]
                    : null,

            'cashier' =>
                $session->user
                    ? [
                        'id' =>
                            $session->user->id,

                        'name' =>
                            $session->user->name,
                    ]
                    : null,
        ],

        'transactions' => [
            'cash' => $cash,
            'qris' => $qris,
            'debit_card' => $debitCard,
            'e_wallet' => $eWallet,
            'transfer' => $transfer,

            'total' => [
                'transaction_count' =>
                    $cash['transaction_count']
                    + $qris['transaction_count']
                    + $debitCard['transaction_count']
                    + $eWallet['transaction_count']
                    + $transfer['transaction_count'],

                'amount' =>
                    $totalSales,
            ],
        ],

        'cash_movement' => [
            'opening_cash' =>
                $openingCash,

            'cash_sales' =>
                $cash['amount'],

            'cash_deposit' =>
                $cashDeposit,

            'expected_cash' =>
                $expectedCash,
        ],

        'reconciliation' => [
            'actual_cash' => 0,
            'difference' => 0,
            'status' => 'balanced',
        ],
    ];
}
   /*
|--------------------------------------------------------------------------
| Close Session
|--------------------------------------------------------------------------
*/

public function close(
    CashierSession $session,
    float $closingBalance,
    ?string $closingNote = null
): CashierSession {

    return DB::transaction(function () use (
        $session,
        $closingBalance,
        $closingNote
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Session
        |--------------------------------------------------------------------------
        */

        $session = CashierSession::query()
            ->whereKey($session->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if (
            $session->status !== 'open'
        ) {
            throw ValidationException::withMessages([
                'session' =>
                    'Cashier session sudah ditutup.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Close
        |--------------------------------------------------------------------------
        */

        $session->update([
            'closed_at' =>
                now(),

            'closing_balance' =>
                $closingBalance,

            'status' =>
                'closed',

            'closing_note' =>
                $closingNote,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Return Fresh Session
        |--------------------------------------------------------------------------
        */

        return $session->fresh([
            'company',
            'branch',
            'warehouse',
            'user',
            'cashAccount',
            'previousSession',
        ]);
    });
}
public function getSessionReport(
    User $user,
    string $dateFrom,
    string $dateTo
): array {
   $branchId = session('current_branch_id');

        if (!$branchId) {
            return [
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
                'summary' => [
                    'sessions' => 0,
                    'open_sessions' => 0,
                    'closed_sessions' => 0,
                    'total_sales' => 0,
                    'cash_sales' => 0,
                ],
                'rows' => [],
            ];
        }

    /*
    |--------------------------------------------------------------------------
    | Sessions
    |--------------------------------------------------------------------------
    */

    $sessions = CashierSession::query()
        ->with([
            'user:id,name',
            'branch:id,code,name',
            'warehouse:id,code,name',
        ])
        ->withSum(
            [
                'sales as total_sales' => function ($query) {
                    $query->where('status', 'posted');
                },
            ],
            'grand_total'
        )
        ->where('company_id', $user->company_id)
        ->where('branch_id', $branchId)
        ->where('user_id', $user->id)
        ->whereDate('opened_at', '>=', $dateFrom)
        ->whereDate('opened_at', '<=', $dateTo)
        ->orderByDesc('opened_at')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Cash Sales
    |--------------------------------------------------------------------------
    |
    | Jangan mengambil grand_total dari pos_sales.
    |
    | Karena satu sale bisa memiliki:
    | Cash + QRIS
    | Cash + Debit
    | dst.
    |
    | Cash Sales harus dihitung dari pos_sale_payments.
    |--------------------------------------------------------------------------
    */

    $cashSales = collect();

    if ($sessions->isNotEmpty()) {
        $cashSales = DB::table('pos_sale_payments')
            ->join(
                'pos_sales',
                'pos_sales.id',
                '=',
                'pos_sale_payments.pos_sale_id'
            )
            ->whereIn(
                'pos_sales.cashier_session_id',
                $sessions->pluck('id')
            )
            ->where('pos_sales.company_id', $user->company_id)
            ->where('pos_sales.branch_id', $branchId)
            ->where('pos_sales.status', 'posted')
            ->where('pos_sale_payments.payment_method', 'cash')
            ->select(
                'pos_sales.cashier_session_id',
                DB::raw(
                    'SUM(pos_sale_payments.amount) as cash_sales'
                )
            )
            ->groupBy('pos_sales.cashier_session_id')
            ->pluck(
                'cash_sales',
                'cashier_session_id'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    $totalSales = (float) $sessions->sum(
        fn ($session) =>
            (float) ($session->total_sales ?? 0)
    );

    $totalCashSales = (float) $cashSales->sum(
        fn ($amount) => (float) $amount
    );

    /*
    |--------------------------------------------------------------------------
    | Rows
    |--------------------------------------------------------------------------
    */

    $rows = $sessions->map(function ($session) use ($cashSales) {
        return [
            'id' => $session->id,

            'session_number' =>
                $session->session_number,

            'cashier' =>
                $session->user?->name ?? '-',

            'branch' => [
                'code' =>
                    $session->branch?->code ?? '-',

                'name' =>
                    $session->branch?->name ?? '-',
            ],

            'warehouse' => [
                'code' =>
                    $session->warehouse?->code ?? '-',

                'name' =>
                    $session->warehouse?->name ?? '-',
            ],

            'opened_at' =>
                $session->opened_at,

            'closed_at' =>
                $session->closed_at,

            'opening_balance' =>
                (float) ($session->opening_balance ?? 0),

            'total_sales' =>
                (float) ($session->total_sales ?? 0),

            'cash_sales' =>
                (float) (
                    $cashSales->get(
                        $session->id,
                        0
                    )
                ),

            'closing_balance' =>
                $session->status === 'closed'
                    ? (float) ($session->closing_balance ?? 0)
                    : null,

            'status' =>
                $session->status,
        ];
    })->values();

    /*
    |--------------------------------------------------------------------------
    | Result
    |--------------------------------------------------------------------------
    */

    return [
        'filters' => [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ],

        'summary' => [
            'sessions' =>
                $sessions->count(),

            'open_sessions' =>
                $sessions->where(
                    'status',
                    'open'
                )->count(),

            'closed_sessions' =>
                $sessions->where(
                    'status',
                    'closed'
                )->count(),

            'total_sales' =>
                $totalSales,

            'cash_sales' =>
                $totalCashSales,
        ],

        'rows' => $rows,
    ];
}
/*
|--------------------------------------------------------------------------
| Get Dashboard Summary
|--------------------------------------------------------------------------
*/

public function getDashboardSummary(
    User $user
): array {
    $currentBranchId =
        session('current_branch_id');

    /*
    |--------------------------------------------------------------------------
    | No Current Branch
    |--------------------------------------------------------------------------
    */

    if (! $currentBranchId) {
        return [
            'today' => [
                'sales' => 0,
                'cash' => 0,
                'payments' => 0,
                'returns' => 0,
            ],

            'recent_transactions' => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Active Session
    |--------------------------------------------------------------------------
    */

    $activeSession =
        CashierSession::query()
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'status',
                'open'
            )
            ->first();

    /*
    |--------------------------------------------------------------------------
    | No Active Session
    |--------------------------------------------------------------------------
    */

    if (! $activeSession) {
        return [
            'today' => [
                'sales' => 0,
                'cash' => 0,
                'payments' => 0,
                'returns' => 0,
            ],

            'recent_transactions' => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Today
    |--------------------------------------------------------------------------
    |
    | Dashboard Session hanya mengambil transaksi dari
    | session cashier yang sedang aktif.
    |
    */

    $todaySales =
        DB::table('pos_sales')
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'cashier_session_id',
                $activeSession->id
            )
            ->where(
                'status',
                'posted'
            )
            ->whereDate(
                'sale_date',
                now()->toDateString()
            );

    $salesCount =
        (clone $todaySales)->count();

    /*
    |--------------------------------------------------------------------------
    | Payment Summary
    |--------------------------------------------------------------------------
    */

    $paymentSummary =
        DB::table('pos_sale_payments')
            ->join(
                'pos_sales',
                'pos_sales.id',
                '=',
                'pos_sale_payments.pos_sale_id'
            )
            ->where(
                'pos_sales.company_id',
                $user->company_id
            )
            ->where(
                'pos_sales.branch_id',
                $currentBranchId
            )
            ->where(
                'pos_sales.cashier_session_id',
                $activeSession->id
            )
            ->where(
                'pos_sales.status',
                'posted'
            )
            ->whereDate(
                'pos_sales.sale_date',
                now()->toDateString()
            )
            ->select(
                DB::raw(
                    'SUM(pos_sale_payments.amount) as total_payments'
                ),
                DB::raw(
                    "SUM(
                        CASE
                            WHEN pos_sale_payments.payment_method = 'cash'
                            THEN pos_sale_payments.amount
                            ELSE 0
                        END
                    ) as cash_sales"
                )
            )
            ->first();

    $totalPayments =
        (float) (
            $paymentSummary->total_payments ?? 0
        );

    $cashSales =
        (float) (
            $paymentSummary->cash_sales ?? 0
        );

    /*
    |--------------------------------------------------------------------------
    | Recent Transactions
    |--------------------------------------------------------------------------
    */

    $recentTransactions =
        DB::table('pos_sales')
            ->leftJoin(
                'pos_sale_payments',
                'pos_sale_payments.pos_sale_id',
                '=',
                'pos_sales.id'
            )
            ->where(
                'pos_sales.company_id',
                $user->company_id
            )
            ->where(
                'pos_sales.branch_id',
                $currentBranchId
            )
            ->where(
                'pos_sales.cashier_session_id',
                $activeSession->id
            )
            ->where(
                'pos_sales.status',
                'posted'
            )
            ->select(
                'pos_sales.id',
                'pos_sales.sale_number',
                'pos_sales.grand_total',
                'pos_sales.sale_date',
                'pos_sales.created_at',
                DB::raw(
                    "GROUP_CONCAT(
                        DISTINCT pos_sale_payments.payment_method
                        ORDER BY pos_sale_payments.payment_method
                        SEPARATOR ' + '
                    ) as payment_method"
                )
            )
            ->groupBy(
                'pos_sales.id',
                'pos_sales.sale_number',
                'pos_sales.grand_total',
                'pos_sales.sale_date',
                'pos_sales.created_at'
            )
            ->orderByDesc(
                'pos_sales.created_at'
            )
            ->limit(5)
            ->get();

    /*
    |--------------------------------------------------------------------------
    | Format Recent Transactions
    |--------------------------------------------------------------------------
    */

    $recentTransactions =
        $recentTransactions
            ->map(function ($transaction) {
                return [
                    'id' =>
                        $transaction->id,

                    'number' =>
                        $transaction->sale_number,

                    'customer' =>
                        null,

                    'time' =>
                        $transaction->created_at,

                    'amount' =>
                        (float) (
                            $transaction->grand_total ?? 0
                        ),

                    'payment_method' =>
                        $transaction->payment_method
                            ?? '-',

                    'status' =>
                        'Posted',
                ];
            })
            ->values()
            ->all();

    /*
    |--------------------------------------------------------------------------
    | Result
    |--------------------------------------------------------------------------
    */

    return [
        'today' => [
            'sales' =>
                $salesCount,

            'cash' =>
                $cashSales,

            'payments' =>
                $totalPayments,

            'returns' =>
                0,
        ],

        'recent_transactions' =>
            $recentTransactions,
    ];
}
/*
|--------------------------------------------------------------------------
| Get Dashboard Analytics
|--------------------------------------------------------------------------
*/

public function getDashboardAnalytics(
    User $user
): array {
    $currentBranchId =
        session('current_branch_id');

    /*
    |--------------------------------------------------------------------------
    | Empty Result
    |--------------------------------------------------------------------------
    */

    $emptyHourly = collect(
        range(0, 23)
    )->map(function ($hour) {
        return [
            'hour' => sprintf('%02d:00', $hour),
            'sales' => 0,
        ];
    })->values()->all();

    if (! $currentBranchId) {
        return [
            'sales_by_hour' => $emptyHourly,
            'top_products' => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Active Session
    |--------------------------------------------------------------------------
    */

    $activeSession =
        CashierSession::query()
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'status',
                'open'
            )
            ->first();

    if (! $activeSession) {
        return [
            'sales_by_hour' => $emptyHourly,
            'top_products' => [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sales By Hour
    |--------------------------------------------------------------------------
    */

    $hourlySales =
        DB::table('pos_sales')
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'cashier_session_id',
                $activeSession->id
            )
            ->where(
                'status',
                'posted'
            )
            ->whereDate(
                'sale_date',
                now()->toDateString()
            )
            ->select(
                DB::raw(
                    'HOUR(created_at) as sale_hour'
                ),
                DB::raw(
                    'SUM(grand_total) as sales'
                )
            )
            ->groupBy(
                DB::raw('HOUR(created_at)')
            )
            ->orderBy(
                DB::raw('HOUR(created_at)')
            )
            ->get()
            ->keyBy('sale_hour');

    $salesByHour =
        collect(range(0, 23))
            ->map(function ($hour) use ($hourlySales) {
                $row =
                    $hourlySales->get($hour);

                return [
                    'hour' =>
                        sprintf(
                            '%02d:00',
                            $hour
                        ),

                    'sales' =>
                        (float) (
                            $row->sales ?? 0
                        ),
                ];
            })
            ->values()
            ->all();

    /*
    |--------------------------------------------------------------------------
    | Top Products
    |--------------------------------------------------------------------------
    */

    $topProducts =
        DB::table('pos_sale_details')
            ->join(
                'pos_sales',
                'pos_sales.id',
                '=',
                'pos_sale_details.pos_sale_id'
            )
            ->join(
                'product_variants',
                'product_variants.id',
                '=',
                'pos_sale_details.product_variant_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'product_variants.product_id'
            )
            ->where(
                'pos_sales.company_id',
                $user->company_id
            )
            ->where(
                'pos_sales.branch_id',
                $currentBranchId
            )
            ->where(
                'pos_sales.cashier_session_id',
                $activeSession->id
            )
            ->where(
                'pos_sales.status',
                'posted'
            )
            ->whereDate(
                'pos_sales.sale_date',
                now()->toDateString()
            )
            ->select(
                'product_variants.id as variant_id',
                'product_variants.sku',
                'product_variants.name as variant_name',
                'products.name as product_name',
                DB::raw(
                    'SUM(pos_sale_details.qty) as qty'
                ),
                DB::raw(
                    'SUM(pos_sale_details.subtotal) as sales'
                )
            )
            ->groupBy(
                'product_variants.id',
                'product_variants.sku',
                'product_variants.name',
                'products.name'
            )
            ->orderByDesc('sales')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'variant_id' =>
                        $product->variant_id,

                    'sku' =>
                        $product->sku,

                    'product_name' =>
                        $product->product_name
                            ?? '-',

                    'variant_name' =>
                        $product->variant_name
                            ?? '-',

                    'qty' =>
                        (float) (
                            $product->qty ?? 0
                        ),

                    'sales' =>
                        (float) (
                            $product->sales ?? 0
                        ),
                ];
            })
            ->values()
            ->all();

    return [
        'sales_by_hour' =>
            $salesByHour,

        'top_products' =>
            $topProducts,
    ];
}
}