<?php

namespace App\Http\Controllers\Purchasing\Reports\PurchaseReport;

use App\Http\Controllers\Controller;
use App\Services\Purchasing\Reports\PurchaseReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Purchasing\Reports\PurchaseReportExport;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Branch;

class PurchaseReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    protected PurchaseReportService $purchaseReportService;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        PurchaseReportService $purchaseReportService
    ) {
        $this->purchaseReportService =
            $purchaseReportService;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => [
                'nullable',
                'integer',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $filters = [
            'company_id' =>
                $request->user()->company_id,

            'branch_id' =>
                $validated['branch_id']
                ?? null,

            'supplier_id' =>
                $validated['supplier_id']
                ?? null,

            'date_from' =>
                $validated['date_from']
                ?? now()
                    ->startOfMonth()
                    ->toDateString(),

            'date_to' =>
                $validated['date_to']
                ?? now()->toDateString(),
        ];

        $report =
            $this->purchaseReportService
                ->getReport($filters);

        return Inertia::render(
            'Purchasing/Reports/PurchaseReport',
            [
                'report' =>
                    $report,

                'filters' =>
                    $filters,

                ...$this->formData(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => [
                'nullable',
                'integer',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $filters = [
            'company_id' =>
                $request->user()->company_id,

            'branch_id' =>
                $validated['branch_id']
                ?? null,

            'supplier_id' =>
                $validated['supplier_id']
                ?? null,

            'date_from' =>
                $validated['date_from']
                ?? now()
                    ->startOfMonth()
                    ->toDateString(),

            'date_to' =>
                $validated['date_to']
                ?? now()->toDateString(),
        ];

        $report =
            $this->purchaseReportService
                ->getReport($filters);

        return Pdf::loadView(
            'pdf.purchasing.reports.purchase-report',
            [
                'report' =>
                    $report,

                'filters' =>
                    $filters,
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                'purchase-report-' .
                $report['date_from'] .
                '-' .
                $report['date_to'] .
                '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Excel
    |--------------------------------------------------------------------------
    */

    public function excel(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => [
                'nullable',
                'integer',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $filters = [
            'company_id' =>
                $request->user()->company_id,

            'branch_id' =>
                $validated['branch_id']
                ?? null,

            'supplier_id' =>
                $validated['supplier_id']
                ?? null,

            'date_from' =>
                $validated['date_from']
                ?? now()
                    ->startOfMonth()
                    ->toDateString(),

            'date_to' =>
                $validated['date_to']
                ?? now()->toDateString(),
        ];

        return Excel::download(
            new PurchaseReportExport(
                $this->purchaseReportService,
                $filters
            ),
            'purchase-report-' .
            $filters['date_from'] .
            '-' .
            $filters['date_to'] .
            '.xlsx'
        );
    }
protected function formData(): array
{
    return [

        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        'suppliers' =>
            Supplier::query()
                ->where(
                    'status',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'supplier_code',
                    'name',
                ])
                ->map(
                    fn ($supplier) => [

                        'id' =>
                            $supplier->id,

                        'code' =>
                            $supplier->supplier_code,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $supplier->supplier_code,
                                    $supplier->name,
                                ])
                            ),

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        'branches' =>
            Branch::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ])
                ->map(
                    fn ($branch) => [

                        'id' =>
                            $branch->id,

                        'label' =>
                            $branch->name,

                    ]
                )
                ->values(),

    ];
}
}