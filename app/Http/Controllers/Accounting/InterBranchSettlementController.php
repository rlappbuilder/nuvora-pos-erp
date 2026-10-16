<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\InterBranchSettlementHeader;
use App\Models\MasterData\Branch;
use App\Services\Accounting\InterBranchSettlementService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class InterBranchSettlementController extends Controller
{
    public function __construct(
        protected InterBranchSettlementService $interBranchSettlementService,
        protected CodeGeneratorService $codeGeneratorService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Company Context
    |--------------------------------------------------------------------------
    */

    private function companyId(): int
    {
        $user = auth()->user();

        abort_unless($user, 401);

        $companyId = (int) $user->company_id;

        abort_unless($companyId > 0, 403, 'User company is not configured.');

        return $companyId;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        $companyId = $this->companyId();

        $query = InterBranchSettlementHeader::query()
            ->byCompany($companyId)
            ->with([
                'fromBranch:id,code,name',
                'toBranch:id,code,name',
                'fromAccount:id,code,name',
                'toAccount:id,code,name',

                // Load detail settlement dan Fund Transfer terkait.
                'details.fundTransfer',

                'paymentJournalEntry:id,code,status',
                'receiptJournalEntry:id,code,status',
                'createdBy:id,name',
                'postedBy:id,name',
                'receivedBy:id,name',
                'activities.performer',
            ]);
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('fromBranch', function ($branchQuery) use ($search) {
                        $branchQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toBranch', function ($branchQuery) use ($search) {
                        $branchQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_branch_id')) {
            $query->where(
                'from_branch_id',
                $request->integer('from_branch_id')
            );
        }

        if ($request->filled('to_branch_id')) {
            $query->where(
                'to_branch_id',
                $request->integer('to_branch_id')
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('receipt_status')) {
            $query->where(
                'receipt_status',
                $request->string('receipt_status')->toString()
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'settlement_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'settlement_date',
                '<=',
                $request->input('date_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'settlement_number',
            'settlement_date',
            'amount',
            'status',
            'receipt_status',
            'created_at',
        ];

        $sortBy = $request->input('sort_by', 'settlement_date');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'settlement_date';
        }

        $sortDirection = strtolower(
            $request->input('sort_direction', 'desc')
        );

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc');

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $settlements = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $baseQuery = InterBranchSettlementHeader::query()
            ->byCompany($companyId);

        $statistics = [
            'total' => (clone $baseQuery)->count(),

            'draft' => (clone $baseQuery)
                ->where('status', 'Draft')
                ->count(),

            'submitted' => (clone $baseQuery)
                ->where('status', 'Submitted')
                ->count(),

            'approved' => (clone $baseQuery)
                ->where('status', 'Approved')
                ->count(),

            'posted' => (clone $baseQuery)
                ->where('status', 'Posted')
                ->count(),

            'pending_receipt' => (clone $baseQuery)
                ->where('status', 'Posted')
                ->where('receipt_status', 'Pending')
                ->count(),

            'received' => (clone $baseQuery)
                ->where('status', 'Posted')
                ->where('receipt_status', 'Received')
                ->count(),

            'total_amount' => (float) (clone $baseQuery)
                ->where('status', 'Posted')
                ->sum('amount'),

            'pending_receipt_amount' => (float) (clone $baseQuery)
                ->where('status', 'Posted')
                ->where('receipt_status', 'Pending')
                ->sum('amount'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Form Data
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return Inertia::render(
            'Accounting/FundTransfer/Settlement/Index',
            [
                'settlements' => $settlements,

                'filters' => $request->only([
                    'search',
                    'from_branch_id',
                    'to_branch_id',
                    'status',
                    'receipt_status',
                    'date_from',
                    'date_to',
                    'sort_by',
                    'sort_direction',
                    'per_page',
                ]),

                'statistics' => $statistics,

                'previewNumber' => $this->codeGeneratorService
                    ->preview('inter_branch_settlement'),

                'branches' => $branches,

                'statuses' => [
                    'Draft',
                    'Submitted',
                    'Approved',
                    'Rejected',
                    'Posted',
                    'Cancelled',
                ],

                'receiptStatuses' => [
                    'Pending',
                    'Received',
                    'Rejected',
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): RedirectResponse
    {
        $this->companyId();

        return redirect()->route('inter-branch-settlements.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->companyId();

        $data = $request->validate([
            'settlement_date' => [
                'required',
                'date',
            ],

            'from_branch_id' => [
                'required',
                'integer',
                'different:to_branch_id',
            ],

            'to_branch_id' => [
                'required',
                'integer',
                'different:from_branch_id',
            ],

            'from_account_id' => [
                'required',
                'integer',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.fund_transfer_header_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'details.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $this->validateBranches(
            $companyId,
            (int) $data['from_branch_id'],
            (int) $data['to_branch_id']
        );

        try {
            $settlement = $this->interBranchSettlementService->create($data);

           return redirect()
            ->route('fund-transfers.index', [
                'tab' => 'payment',
            ])
            ->with(
                'success',
                "Inter-Branch Settlement {$settlement->settlement_number} berhasil dibuat."
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'settlement' => $e->getMessage(),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Eligible Fund Transfers
    |--------------------------------------------------------------------------
    |
    | from_branch_id = cabang yang membayar / berutang
    | to_branch_id   = cabang penerima pembayaran / pemberi dana
    |--------------------------------------------------------------------------
    */

    public function eligibleTransfers(Request $request): JsonResponse
    {
        $companyId = $this->companyId();

        $data = $request->validate([
            'from_branch_id' => [
                'required',
                'integer',
            ],

            'to_branch_id' => [
                'required',
                'integer',
                'different:from_branch_id',
            ],
        ]);

        $this->validateBranches(
            $companyId,
            (int) $data['from_branch_id'],
            (int) $data['to_branch_id']
        );

        $transfers = $this->interBranchSettlementService
            ->eligibleFundTransfers(
                $companyId,
                (int) $data['from_branch_id'],
                (int) $data['to_branch_id']
            );

        return response()->json([
            'data' => $transfers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Account Options
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk memilih rekening pembayaran atau rekening penerimaan.
    |--------------------------------------------------------------------------
    */

    public function accounts(Request $request): JsonResponse
    {
        $companyId = $this->companyId();

        $data = $request->validate([
            'branch_id' => [
                'required',
                'integer',
            ],
        ]);

        $branchId = (int) $data['branch_id'];

        Branch::query()
            ->where('company_id', $companyId)
            ->findOrFail($branchId);

        $accounts = BranchAccountMapping::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('key', 'cash_coh')
                    ->orWhere('key', 'like', 'cash_bank_%');
            })
            ->get()
            ->filter(function ($mapping) use ($companyId) {
                $account = $mapping->account;

                return $account
                    && (int) $account->company_id === $companyId
                    && (bool) $account->is_posting
                    && (bool) $account->status;
            })
            ->unique('account_id')
            ->values()
            ->map(function ($mapping) {
                return [
                    'id' => $mapping->account->id,
                    'code' => $mapping->account->code,
                    'name' => $mapping->account->name,
                    'key' => $mapping->key,
                ];
            });

        return response()->json([
            'data' => $accounts,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        InterBranchSettlementHeader $settlement
    ): Response {
        $settlement = $this->findSettlement($settlement->id);

        return Inertia::render(
            'Accounting/FundTransfer/Settlement/View',
            [
                'settlement' => $settlement,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        InterBranchSettlementHeader $settlement
    ): JsonResponse {
        $settlement = $this->findSettlement($settlement->id);

        return response()->json([
            'data' => $settlement,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        $data = $request->validate([
            'settlement_date' => [
                'required',
                'date',
            ],

            'from_branch_id' => [
                'required',
                'integer',
                'different:to_branch_id',
            ],

            'to_branch_id' => [
                'required',
                'integer',
                'different:from_branch_id',
            ],

            'from_account_id' => [
                'required',
                'integer',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.fund_transfer_header_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'details.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $this->validateBranches(
            (int) $settlement->company_id,
            (int) $data['from_branch_id'],
            (int) $data['to_branch_id']
        );

        try {
            $this->interBranchSettlementService->update(
                $settlement,
                $data
            );

            return redirect()
            ->route('fund-transfers.index', [
                'tab' => 'payment',
            ])
            ->with(
                'success',
                'Inter-Branch Settlement berhasil diperbarui.'
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'settlement' => $e->getMessage(),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        try {
            $this->interBranchSettlementService->submit($settlement);

            return back()->with(
                'success',
                'Inter-Branch Settlement berhasil diajukan.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        try {
            $this->interBranchSettlementService->approve($settlement);

            return back()->with(
                'success',
                'Inter-Branch Settlement berhasil disetujui.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ]);

        try {
            $this->interBranchSettlementService->reject(
                $settlement,
                $data['reason']
            );

            return back()->with(
                'success',
                'Inter-Branch Settlement berhasil ditolak.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Post Payment
    |--------------------------------------------------------------------------
    */

    public function post(
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        try {
            $this->interBranchSettlementService->post($settlement);

            return back()->with(
                'success',
                'Pembayaran berhasil di-posting. Menunggu penerimaan cabang tujuan.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

public function receive(
    Request $request,
    InterBranchSettlementHeader $settlement
) {
    $data = $request->validate([
        'to_account_id' => [
            'required',
            'integer',
            'exists:chart_of_accounts,id',
        ],
        'received_date' => [
            'required',
            'date_format:Y-m-d',
        ],
    ]);

    try {
        $this->interBranchSettlementService->receive(
            $settlement,
            (int) $data['to_account_id'],
            $data['received_date']
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Inter-branch settlement received successfully.'
            );
    } catch (\Throwable $e) {
        return redirect()
            ->back()
            ->withErrors([
                'inter_branch_settlement' => $e->getMessage(),
            ])
            ->withInput();
    }
}

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        $data = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ]);

        try {
            $this->interBranchSettlementService->cancel(
                $settlement,
                $data['reason']
            );

            return back()->with(
                'success',
                'Inter-Branch Settlement berhasil dibatalkan.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        InterBranchSettlementHeader $settlement
    ): RedirectResponse {
        $settlement = $this->findSettlement($settlement->id);

        try {
            $this->interBranchSettlementService->delete($settlement);

            return back()->with(
                'success',
                'Inter-Branch Settlement berhasil dihapus.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'settlement' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function findSettlement(
        int $settlementId
    ): InterBranchSettlementHeader {
        return InterBranchSettlementHeader::query()
            ->byCompany($this->companyId())
            ->with([
                'fromBranch:id,code,name',
                'toBranch:id,code,name',
                'fromAccount:id,code,name',
                'toAccount:id,code,name',
                'details.fundTransfer',
                'paymentJournalEntry',
                'receiptJournalEntry',
                'submittedBy:id,name',
                'approvedBy:id,name',
                'rejectedBy:id,name',
                'postedBy:id,name',
                'receivedBy:id,name',
                'cancelledBy:id,name',
                'createdBy:id,name',
                'updatedBy:id,name',
                'activities.performer',
            ])
            ->findOrFail($settlementId);
    }

    private function validateBranches(
        int $companyId,
        int $fromBranchId,
        int $toBranchId
    ): void {
        if ($fromBranchId === $toBranchId) {
            throw new RuntimeException(
                'Cabang pembayar dan cabang penerima harus berbeda.'
            );
        }

        Branch::query()
            ->where('company_id', $companyId)
            ->findOrFail($fromBranchId);

        Branch::query()
            ->where('company_id', $companyId)
            ->findOrFail($toBranchId);
    }

    /*
|--------------------------------------------------------------------------
| Print Preview
|--------------------------------------------------------------------------
*/

public function print(
    InterBranchSettlementHeader $settlement
): Response {
    $settlement = $this->findSettlement($settlement->id);

    return Inertia::render(
        'Accounting/FundTransfer/Settlement/Print',
        [
            'settlement' => $settlement,
        ]
    );
}
}