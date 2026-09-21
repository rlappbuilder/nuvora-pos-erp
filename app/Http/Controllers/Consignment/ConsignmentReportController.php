<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\Reseller\Reseller;

use App\Services\Consignment\ConsignmentReportService;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\Resellers\Reports\ConsignmentReportExport;

class ConsignmentReportController extends Controller
{
    public function __construct(
        protected ConsignmentReportService $consignmentReportService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $this->getFilters($request);

        $report =
            $this->consignmentReportService
                ->getReport($filters);


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($branch) => [
                'id' =>
                    $branch->id,

                'label' =>
                    $branch->name,
            ])
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Resellers
        |--------------------------------------------------------------------------
        */

        $resellers = Reseller::query()
            ->where('status', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($reseller) => [
                'id' =>
                    $reseller->id,

                'label' =>
                    $reseller->name,

                'code' =>
                    $reseller->reseller_code,
            ])
            ->values();


        return Inertia::render(
            'Resellers/ConsignmentReport/Index',
            [
                'title' =>
                    'Consignment Report',

                'report' =>
                    $report,

                'branches' =>
                    $branches,

                'resellers' =>
                    $resellers,

                'filters' =>
                    $filters,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $filters = $this->getFilters($request);

        $report =
            $this->consignmentReportService
                ->getReport($filters);

        return view(
            'print.Reseller.Reports.consignment-report',
            [
                'report' =>
                    $report,

                'filters' =>
                    $filters,
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
        $filters = $this->getFilters($request);

        $report =
            $this->consignmentReportService
                ->getReport($filters);

        return Pdf::loadView(
            'pdf.Reseller.Reports.consignment-report',
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
            'consignment-report.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Excel
    |--------------------------------------------------------------------------
    */

    public function excel(Request $request)
    {
        $filters = $this->getFilters($request);

        return Excel::download(
            new ConsignmentReportExport($filters),
            'consignment-report.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    private function getFilters(Request $request): array
    {
        return $request->only([
            'report_type',
            'date_from',
            'date_to',
            'reseller_id',
            'branch_id',
        ]);
    }
}