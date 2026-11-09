<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionOperation;
use App\Http\Requests\Production\ProductionOperation\StoreProductionOperationRequest;
use App\Http\Requests\Production\ProductionOperation\UpdateProductionOperationRequest;
use App\Services\Production\ProductionOperationService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionOperationController extends Controller
{
    protected string $module = 'Production Operation';

    public function __construct(
        protected ProductionOperationService $productionOperationService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    protected function formData(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

       $query = ProductionOperation::query()
        ->with([
            'creator:id,name',
            'updater:id,name',
        ])
        ->where('company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('is_active')) {

            $query->where(
                'is_active',
                $request->is_active
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'code',
            'name',
            'sort_order',
            'is_active',
            'created_at',
        ];

        $sortBy = in_array(
            $request->sort_by,
            $allowedSorts
        )
            ? $request->sort_by
            : 'sort_order';

        $sortDirection = $request->sort_direction === 'desc'
            ? 'desc'
            : 'asc';

        $query->orderBy(
            $sortBy,
            $sortDirection
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = in_array(
            (int) $request->per_page,
            [10, 20, 30, 40, 50]
        )
            ? (int) $request->per_page
            : 10;

        $operations = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = ProductionOperation::query()
            ->where('company_id', $companyId);

        $statistics = [

            'total' =>
                (clone $statisticsQuery)->count(),

            'active' =>
                (clone $statisticsQuery)
                    ->where('is_active', true)
                    ->count(),

            'inactive' =>
                (clone $statisticsQuery)
                    ->where('is_active', false)
                    ->count(),

        ];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Production/ProductionOperation/Index',
            [

                'title' =>
                    $this->module,

                'operations' =>
                    $operations,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this->codeGeneratorService
                        ->preview('production_operation'),

                'filters' => [

                    'search' =>
                        $request->search,

                    'is_active' =>
                        $request->is_active,

                    'sort_by' =>
                        $sortBy,

                    'sort_direction' =>
                        $sortDirection,

                    'per_page' =>
                        $perPage,

                ],

                'formData' =>
                    $this->formData(),

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Production/ProductionOperation/Form',
            [

                'title' =>
                    'Create Production Operation',

                'operation' =>
                    null,

                'previewNumber' =>
                    $this->codeGeneratorService
                        ->preview('production_operation'),

                'formData' =>
                    $this->formData(),

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreProductionOperationRequest $request
    ) {

        $data = $request->validated();

        $data['company_id'] =
            auth()->user()->company_id;

        $data['created_by'] =
            auth()->id();

        $this->productionOperationService
            ->store($data);

        return redirect()
            ->route('production-operations.index')
            ->with(
                'success',
                'Production operation created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ProductionOperation $production_operation
    ) {

        abort_unless(
            $production_operation->company_id === auth()->user()->company_id,
            404
        );

        $operation = $this->productionOperationService
            ->find($production_operation);

        return Inertia::render(
            'Production/ProductionOperation/Show',
            [

                'title' =>
                    'Production Operation Detail',

                'operation' =>
                    $operation,

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ProductionOperation $production_operation
    ) {

        abort_unless(
            $production_operation->company_id === auth()->user()->company_id,
            404
        );

        return Inertia::render(
            'Production/ProductionOperation/Form',
            [

                'title' =>
                    'Edit Production Operation',

                'operation' =>
                    $production_operation,

                'formData' =>
                    $this->formData(),

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateProductionOperationRequest $request,
        ProductionOperation $production_operation
    ) {

        abort_unless(
            $production_operation->company_id === auth()->user()->company_id,
            404
        );

        $data = $request->validated();

        $data['company_id'] =
            auth()->user()->company_id;

        $data['updated_by'] =
            auth()->id();

        $this->productionOperationService
            ->update(
                $production_operation,
                $data
            );

        return redirect()
            ->route('production-operations.index')
            ->with(
                'success',
                'Production operation updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductionOperation $production_operation
    ) {

        abort_unless(
            $production_operation->company_id === auth()->user()->company_id,
            404
        );

        $production_operation->update([
            'deleted_by' => auth()->id(),
        ]);

        $this->productionOperationService
            ->destroy($production_operation);

        return redirect()
            ->back()
            ->with(
                'success',
                'Production operation deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(Request $request)
    {
        $request->validate([

            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
            ],

        ]);

        $companyId = auth()->user()->company_id;

        $ids = ProductionOperation::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $request->ids)
            ->pluck('id')
            ->toArray();

        $this->productionOperationService
            ->bulkDelete($ids);

        return redirect()
            ->back()
            ->with(
                'success',
                'Production operations deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Activate
    |--------------------------------------------------------------------------
    */

    public function bulkActivate(Request $request)
    {
        $request->validate([

            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
            ],

        ]);

        $companyId = auth()->user()->company_id;

        $ids = ProductionOperation::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $request->ids)
            ->pluck('id')
            ->toArray();

        $this->productionOperationService
            ->bulkActivate($ids);

        return redirect()
            ->back()
            ->with(
                'success',
                'Production operations activated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Deactivate
    |--------------------------------------------------------------------------
    */

    public function bulkDeactivate(Request $request)
    {
        $request->validate([

            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
            ],

        ]);

        $companyId = auth()->user()->company_id;

        $ids = ProductionOperation::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $request->ids)
            ->pluck('id')
            ->toArray();

        $this->productionOperationService
            ->bulkDeactivate($ids);

        return redirect()
            ->back()
            ->with(
                'success',
                'Production operations deactivated successfully.'
            );
    }
}