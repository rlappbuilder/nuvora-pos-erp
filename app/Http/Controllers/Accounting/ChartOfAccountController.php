<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreChartOfAccountRequest;
use App\Http\Requests\Accounting\UpdateChartOfAccountRequest;
use App\Models\Accounting\ChartOfAccount;
use App\Services\Accounting\ChartOfAccountService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChartOfAccountController extends Controller
{
    public function __construct(
        protected ChartOfAccountService $chartOfAccountService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Display Listing
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        return Inertia::render(
            'Accounting/ChartOfAccount/Index',
            $this->chartOfAccountService->indexData($request)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Accounting/ChartOfAccount/Create',
            $this->chartOfAccountService->formData()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(StoreChartOfAccountRequest $request)
    {
        $this->chartOfAccountService->store(
            $request->validated()
        );

        if ($request->boolean('create_another')) {

            return redirect()
                ->route('chart-of-accounts.create')
                ->with(
                    'success',
                    'Chart of Account created successfully.'
                );
        }

        return redirect()
            ->route('chart-of-accounts.index')
            ->with(
                'success',
                'Chart of Account created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ChartOfAccount $chartOfAccount
    )
    {
        return Inertia::render(

            'Accounting/ChartOfAccount/Show',

            $this->chartOfAccountService->printData(
                $chartOfAccount
            )

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form
    |--------------------------------------------------------------------------
    */

    public function edit(
        ChartOfAccount $chartOfAccount
    )
    {
        return Inertia::render(

            'Accounting/ChartOfAccount/Edit',

            array_merge(

                $this->chartOfAccountService->formData(),

                [

                    'chartOfAccount' => $chartOfAccount,

                ]

            )

        );
    }
/*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateChartOfAccountRequest $request,
        ChartOfAccount $chartOfAccount
    )
    {
        $this->chartOfAccountService->update(
            $chartOfAccount,
            $request->validated()
        );

        return redirect()
            ->route('chart-of-accounts.index')
            ->with(
                'success',
                'Chart of Account updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ChartOfAccount $chartOfAccount
    )
    {
        $this->chartOfAccountService->destroy(
            $chartOfAccount
        );

        return redirect()
            ->route('chart-of-accounts.index')
            ->with(
                'success',
                'Chart of Account deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(Request $request)
    {
        $this->chartOfAccountService->bulkDelete(
            $request->ids
        );

        return back()->with(
            'success',
            'Selected Chart of Account deleted successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Activate
    |--------------------------------------------------------------------------
    */

    public function bulkActivate(Request $request)
    {
        $this->chartOfAccountService->bulkActivate(
            $request->ids
        );

        return back()->with(
            'success',
            'Selected Chart of Account activated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Deactivate
    |--------------------------------------------------------------------------
    */

    public function bulkDeactivate(Request $request)
    {
        $this->chartOfAccountService->bulkDeactivate(
            $request->ids
        );

        return back()->with(
            'success',
            'Selected Chart of Account deactivated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(
        ChartOfAccount $chartOfAccount
    )
    {
        return Inertia::render(

            'Accounting/ChartOfAccount/Create',

            $this->chartOfAccountService->duplicate(
                $chartOfAccount
            )

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        ChartOfAccount $chartOfAccount
    )
    {
        return Inertia::render(

            'Accounting/ChartOfAccount/Print',

            $this->chartOfAccountService->printData(
                $chartOfAccount
            )

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export
    |--------------------------------------------------------------------------
    */

    public function export(
        ChartOfAccount $chartOfAccount
    )
    {
        return $this->chartOfAccountService->export(
            $chartOfAccount
        );
    }

public function previewCode(Request $request)
{
    $code = $this->chartOfAccountService->generateCode(
        $request->integer('parent_id')
    );

    return response()->json([
        'code' => $code,
    ]);
}
}