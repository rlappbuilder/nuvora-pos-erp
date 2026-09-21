<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\Reseller\Reseller;

use App\Services\Consignment\ResellerMutationService;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\Resellers\Reports\ResellerMutationExport;

class ResellerMutationController extends Controller
{
    public function __construct(
        protected ResellerMutationService $resellerMutationService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = $request->only([
            'date_from',
            'date_to',
            'reseller_id',
            'branch_id',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Report
        |--------------------------------------------------------------------------
        */

        $report =
            $this->resellerMutationService
                ->getReport($filters);


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Branch::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($branch) => [

                        'id' =>
                            $branch->id,

                        'label' =>
                            $branch->name,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Resellers
        |--------------------------------------------------------------------------
        */

        $resellers =
            Reseller::query()
                ->where(
                    'status',
                    true
                )
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($reseller) => [

                        'id' =>
                            $reseller->id,

                        'label' =>
                            $reseller->name,

                        'code' =>
                            $reseller->reseller_code,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Resellers/ResellerMutation/Index',
            [

                'title' =>
                    'Reseller Mutation',

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
    | Export Filters
    |--------------------------------------------------------------------------
    */

    private function exportFilters(
        Request $request
    ): array {

        return $request->only([
            'date_from',
            'date_to',
            'reseller_id',
            'branch_id',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Export Data
    |--------------------------------------------------------------------------
    */

    private function getExportData(
        Request $request
    ): array {

        return $this->resellerMutationService
            ->getReport(
                $this->exportFilters(
                    $request
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request
    ) {

        $report =
            $this->getExportData(
                $request
            );


        return view(
            'print.Reseller.Reports.reseller-mutation',
            [

                'report' =>
                    $report,

                'filters' =>
                    $this->exportFilters(
                        $request
                    ),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Excel
    |--------------------------------------------------------------------------
    */

    public function excel(
        Request $request
    ) {

        return Excel::download(
            new ResellerMutationExport(
                $this->exportFilters(
                    $request
                )
            ),
            'reseller-mutation.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(
        Request $request
    ) {

        $report =
            $this->getExportData(
                $request
            );


        return Pdf::loadView(
            'pdf.Reseller.Reports.reseller-mutation',
            [

                'report' =>
                    $report,

                'filters' =>
                    $this->exportFilters(
                        $request
                    ),

            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                'reseller-mutation.pdf'
            );
    }
}