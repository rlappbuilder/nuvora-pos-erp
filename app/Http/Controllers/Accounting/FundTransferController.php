<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\FundTransfer\StoreFundTransferRequest;
use App\Http\Requests\Accounting\FundTransfer\UpdateFundTransferRequest;
use App\Http\Requests\Accounting\FundTransfer\RejectFundTransferRequest;
use App\Models\Accounting\FundTransferHeader;
use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\BranchAccountMapping;
//use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Services\Accounting\FundTransferService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use RuntimeException;

class FundTransferController extends Controller
{
    protected FundTransferService $fundTransferService;

    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        FundTransferService $fundTransferService,
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->fundTransferService = $fundTransferService;
        $this->codeGeneratorService = $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = FundTransferHeader::query()
            ->with([
                'company',
                'fromBranch',
                'fromAccount',
                'toBranch',
                'toAccount',
                'journalEntry',
                'createdBy',
                'submittedBy',
                'approvedBy',
                'rejectedBy',
                'postedBy',
                'cancelledBy',
            ])
            ->where('company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('fromBranch', function ($branch) use ($search) {
                        $branch->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toBranch', function ($branch) use ($search) {
                        $branch->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fromAccount', function ($account) use ($search) {
                        $account->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toAccount', function ($account) use ($search) {
                        $account->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
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
                $request->from_branch_id
            );
        }

        if ($request->filled('to_branch_id')) {
            $query->where(
                'to_branch_id',
                $request->to_branch_id
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'transfer_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'transfer_date',
                '<=',
                $request->date_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $fundTransfers = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = FundTransferHeader::query()
            ->where('company_id', $companyId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'Draft') as draft")
            ->selectRaw("SUM(status = 'Submitted') as submitted")
            ->selectRaw("SUM(status = 'Approved') as approved")
            ->selectRaw("SUM(status = 'Rejected') as rejected")
            ->selectRaw("SUM(status = 'Posted') as posted")
            ->selectRaw("SUM(status = 'Cancelled') as cancelled")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Accounting/FundTransfer/Index', [
            'title' => 'Fund Transfer',
            'fundTransfers' => $fundTransfers,
            'stats' => $stats,
            'previewNumber' => $this->codeGeneratorService->preview(
                'fund_transfer'
            ),
            'filters' => $request->only([
                'search',
                'from_branch_id',
                'to_branch_id',
                'status',
                'date_from',
                'date_to',
                'per_page',
            ]),
            ...$this->formData(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get([
                    'id',
                    'company_id',
                    'code',
                    'name',
                ]),

            'statuses' => [
                'Draft',
                'Submitted',
                'Approved',
                'Rejected',
                'Posted',
                'Cancelled',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    |
    | Mengambil akun berdasarkan konteks perusahaan atau cabang.
    | Konteks perusahaan harus dikirim secara eksplisit agar tidak
    | tertukar dengan pilihan cabang yang belum dipilih.
    |--------------------------------------------------------------------------
    */

    public function accounts(Request $request)
{
    $companyId = auth()->user()->company_id;

    $validated = $request->validate([
        'context' => ['required', 'in:company,branch'],
        'branch_id' => ['required_if:context,branch', 'nullable', 'integer'],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Company Accounts
    |--------------------------------------------------------------------------
    */
    if ($validated['context'] === 'company') {
        $mapping = AccountMapping::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('key', 'company_cash')
            ->where('is_active', true)
            ->first();

        if (! $mapping || ! $mapping->account) {
            return response()->json([]);
        }

        return response()->json([
            [
                'id' => $mapping->account->id,
                'code' => $mapping->account->code,
                'name' => $mapping->account->name,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Accounts
    |--------------------------------------------------------------------------
    */
    $branch = Branch::query()
        ->where('company_id', $companyId)
        ->findOrFail($validated['branch_id']);

    $mappings = BranchAccountMapping::query()
        ->with('account')
        ->where('company_id', $companyId)
        ->where('branch_id', $branch->id)
        ->where('is_active', true)
        ->where(function ($query) {
            $query->where('key', 'cash_coh')
                ->orWhere('key', 'like', 'cash_bank_%');
        })
        ->get();

    return response()->json(
        $mappings
            ->filter(fn ($mapping) => $mapping->account !== null)
            ->map(fn ($mapping) => [
                'id' => $mapping->account->id,
                'code' => $mapping->account->code,
                'name' => $mapping->account->name,
            ])
            ->unique('id')
            ->values()
    );
}

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render('Accounting/FundTransfer/Create', [
            'title' => 'Create Fund Transfer',
            'previewNumber' => $this->codeGeneratorService->preview(
                'fund_transfer'
            ),
            ...$this->formData(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(StoreFundTransferRequest $request)
    {
        try {
            $fundTransfer = $this->fundTransferService->create(
                $request->validated()
            );

            return redirect()
                ->route('fund-transfers.index')
                ->with(
                    'success',
                    'Fund transfer created successfully.'
                )
                ->with(
                    'fund_transfer_id',
                    $fundTransfer->id
                );
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'fund_transfer' => $e->getMessage(),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        $fundTransfer->load([
            'company',
            'fromBranch',
            'fromAccount',
            'toBranch',
            'toAccount',
            'journalEntry.lines.account',
            'createdBy',
            'updatedBy',
            'submittedBy',
            'approvedBy',
            'rejectedBy',
            'postedBy',
            'cancelledBy',
            'activities.performer',
        ]);

        return Inertia::render(
            'Accounting/FundTransfer/Show',
            [
                'title' => 'Fund Transfer Detail',
                'fundTransfer' => $fundTransfer,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        return response()->json(
            $fundTransfer->load([
                'fromBranch',
                'fromAccount',
                'toBranch',
                'toAccount',
                'journalEntry.lines.account',
                'activities.performer',
            ])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateFundTransferRequest $request,
        FundTransferHeader $fundTransfer
    ) {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->update(
                $fundTransfer,
                $request->validated()
            );

            return redirect()
                ->route('fund-transfers.index')
                ->with(
                    'success',
                    'Fund transfer updated successfully.'
                );
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'fund_transfer' => $e->getMessage(),
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->submit($fundTransfer);

            return back()->with(
                'success',
                'Fund transfer submitted successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->approve($fundTransfer);

            return back()->with(
                'success',
                'Fund transfer approved successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectFundTransferRequest $request,
        FundTransferHeader $fundTransfer
    ) {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->reject(
                $fundTransfer,
                $request->validated('reason')
            );

            return back()->with(
                'success',
                'Fund transfer rejected successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->post($fundTransfer);

            return back()->with(
                'success',
                'Fund transfer posted successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(Request $request, FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ]);

        try {
            $this->fundTransferService->cancel(
                $fundTransfer,
                $validated['reason']
            );

            return back()->with(
                'success',
                'Fund transfer cancelled successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(FundTransferHeader $fundTransfer)
    {
        $this->authorizeCompany($fundTransfer);

        try {
            $this->fundTransferService->delete($fundTransfer);

            return back()->with(
                'success',
                'Fund transfer deleted successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],
            'ids.*' => [
                'integer',
                'distinct',
            ],
        ]);

        $companyId = auth()->user()->company_id;

        try {
            DB::transaction(function () use (
                $validated,
                $companyId
            ) {
                $fundTransfers = FundTransferHeader::query()
                    ->where('company_id', $companyId)
                    ->whereIn('id', $validated['ids'])
                    ->lockForUpdate()
                    ->get();

                if ($fundTransfers->count() !== count($validated['ids'])) {
                    throw new RuntimeException(
                        'One or more fund transfers were not found.'
                    );
                }

                foreach ($fundTransfers as $fundTransfer) {
                    $this->fundTransferService->delete($fundTransfer);
                }
            });

            return back()->with(
                'success',
                'Selected fund transfers deleted successfully.'
            );
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'fund_transfer' => $e->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company Guard
    |--------------------------------------------------------------------------
    */

    private function authorizeCompany(
        FundTransferHeader $fundTransfer
    ): void {
        abort_unless(
            (int) $fundTransfer->company_id ===
                (int) auth()->user()->company_id,
            404
        );
    }
}