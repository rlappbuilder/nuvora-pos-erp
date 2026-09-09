<?php

namespace App\Http\Controllers\Purchasing\Reports\APAging;

use App\Http\Controllers\Controller;
use App\Services\Purchasing\Reports\APAgingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Purchasing\Reports\APAgingExport;
class APAgingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    protected APAgingService $apAgingService;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        APAgingService $apAgingService
    ) {
        $this->apAgingService =
            $apAgingService;
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

            'as_of_date' => [
                'nullable',
                'date',
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

            'as_of_date' =>
                $validated['as_of_date']
                ?? now()->toDateString(),
        ];

        $report =
            $this->apAgingService
                ->getAging($filters);

        return Inertia::render(
            'Purchasing/Reports/APAging',
            [
                'report' =>
                    $report,

                'filters' =>
                    $filters,
            ]
        );
    }

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

        'as_of_date' => [
            'nullable',
            'date',
        ],
    ]);

    $filters = [
        'company_id' =>
            $request->user()->company_id,

        'branch_id' =>
            $validated['branch_id'] ?? null,

        'supplier_id' =>
            $validated['supplier_id'] ?? null,

        'as_of_date' =>
            $validated['as_of_date']
            ?? now()->toDateString(),
    ];

    $report =
        $this->apAgingService
            ->getAging($filters);

    return Pdf::loadView(
        'pdf.purchasing.reports.ap-aging',
        [
            'report' => $report,
            'filters' => $filters,
        ]
    )
        ->setPaper('a4', 'landscape')
        ->download(
            'ap-aging-' .
            $report['as_of_date'] .
            '.pdf'
        );
}

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

        'as_of_date' => [
            'nullable',
            'date',
        ],
    ]);

    $filters = [
        'company_id' =>
            $request->user()->company_id,

        'branch_id' =>
            $validated['branch_id'] ?? null,

        'supplier_id' =>
            $validated['supplier_id'] ?? null,

        'as_of_date' =>
            $validated['as_of_date']
            ?? now()->toDateString(),
    ];

    return Excel::download(
        new APAgingExport(
            $this->apAgingService,
            $filters
        ),
        'ap-aging-' .
        ($filters['as_of_date']) .
        '.xlsx'
    );
}
}