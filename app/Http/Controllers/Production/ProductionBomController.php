<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\Bom\StoreProductionBomRequest;
use App\Http\Requests\Production\Bom\UpdateProductionBomRequest;
use App\Models\Production\ProductionBom;
use App\Models\Production\ProductionBomVersion;
use App\Models\Production\ProductionOperation;
use App\Services\Core\CodeGeneratorService;
use App\Services\Production\ProductionBomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductionBomController extends Controller
{
    protected ProductionBomService $productionBomService;

    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        ProductionBomService $productionBomService,
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->productionBomService = $productionBomService;
        $this->codeGeneratorService = $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Code
    |--------------------------------------------------------------------------
    */

    public function previewCode()
    {
        return response()->json([
            'code' => $this->codeGeneratorService->preview(
                'production_bom'
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $filters = [
            'search' => $request->input('search'),
            'branch_id' => $request->input('branch_id'),
            'product_variant_id' => $request->input('product_variant_id'),
            'bom_type' => $request->input('bom_type'),
            'status' => $request->input('status'),
            'per_page' => $request->input('per_page', 10),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $query = ProductionBom::query()
            ->with([
                'branch',
                'productVariant.product',

                'versions.outputUnit',
                'versions.approver',

                'versions.operations.productionOperation',

                'versions.materials.componentVariant.product',
                'versions.materials.unit',
                'versions.materials.productionBomOperation.productionOperation',
            ])
            ->where('company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when($filters['search'], function ($q, $search) {

            $q->where(function ($sub) use ($search) {

                $sub->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'productVariant',
                        function ($variant) use ($search) {
                            $variant->where('sku', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'product',
                                    fn ($product) => $product->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $query->when(
            $filters['branch_id'],
            fn ($q, $branchId) => $q->where(
                'branch_id',
                $branchId
            )
        );

        $query->when(
            $filters['product_variant_id'],
            fn ($q, $variantId) => $q->where(
                'product_variant_id',
                $variantId
            )
        );

        $query->when(
            $filters['bom_type'],
            fn ($q, $type) => $q->where(
                'bom_type',
                $type
            )
        );

        $query->when($filters['status'], function ($q, $status) {

            if ($status === 'active') {
                $q->where('is_active', true)
                    ->whereHas('versions', function ($version) {
                        $version->where('status', 'active');
                    });
            }

            if ($status === 'inactive') {
                $q->where('is_active', false);
            }

            if ($status === 'draft') {
                $q->whereHas('versions', function ($version) {
                    $version->where('status', 'draft');
                });
            }

        });

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'code',
            'name',
            'bom_type',
            'is_active',
            'created_at',
        ];

        $sort = in_array($filters['sort'], $allowedSorts, true)
            ? $filters['sort']
            : 'created_at';

        $direction = strtolower($filters['direction']) === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sort, $direction);

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = ProductionBom::query()
            ->where('company_id', $companyId);

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'active' => (clone $statisticsQuery)
                ->where('is_active', true)
                ->count(),

            'draft' => (clone $statisticsQuery)
                ->whereHas('versions', function ($version) {
                    $version->where('status', 'draft');
                })
                ->count(),

            'inactive' => (clone $statisticsQuery)
                ->where('is_active', false)
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $filters['per_page'];

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $boms = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Production/Bom/Index', [
            'title' => 'Bill of Materials',
            'boms' => $boms,
            'statistics' => $statistics,
            'branches' => $this->getBranches(),
            'variants' => $this->getProductVariants(),
            'filters' => $filters,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    protected function formData(
        ?ProductionBom $bom = null
    ): array {

        $companyId = auth()->user()->company_id;

        return [
            'previewCode' => $this->codeGeneratorService->preview(
                'production_bom'
            ),

            'branches' => $this->getBranches(),

            // Finished Goods untuk BOM Output
            'outputVariants' => $this->getProductVariants(
                'finished_goods'
            ),

            // Raw Material untuk BOM Material
            'materialVariants' => $this->getProductVariants(
                'raw_material'
            ),

            'variantUnits' => $this->getProductVariantUnits(),

            'productionOperations' => ProductionOperation::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                ]),

            'bom' => $bom
                ? $this->productionBomService->getBomDetail($bom)
                : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Production/Bom/Create',
            array_merge(
                [
                    'title' => 'Create Bill of Materials',
                    'mode' => 'create',
                ],
                $this->formData()
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreProductionBomRequest $request
    ) {

        $bom = $this->productionBomService->createBom(
            $request->validated()
        );

        return redirect()
            ->route('production-boms.show', $bom->id)
            ->with('success', 'Bill of Materials created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $bom = $this->productionBomService->getBomDetail($bom);

        return Inertia::render('Production/Bom/Show', [
            'title' => 'Bill of Materials Detail',
            'bom' => $bom,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $bom = $this->productionBomService->getBomDetail($bom);

        $draftVersion = $bom->versions
            ->firstWhere('status', 'draft');

        abort_unless($draftVersion, 404);

        return Inertia::render(
            'Production/Bom/Edit',
            array_merge(
                [
                    'title' => 'Edit Bill of Materials',
                    'mode' => 'edit',
                    'draftVersion' => $draftVersion,
                ],
                $this->formData($bom)
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateProductionBomRequest $request,
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $bom = $this->productionBomService->updateDraft(
            $bom,
            $request->validated()
        );

        return redirect()
            ->route('production-boms.show', $bom->id)
            ->with('success', 'Bill of Materials updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Revision
    |--------------------------------------------------------------------------
    */

    public function createRevision(
        Request $request,
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $data = $request->validate([
            'revision_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $version = $this->productionBomService->createRevision(
            $bom,
            $data
        );

        return redirect()
            ->route('production-boms.edit', $bom->id)
            ->with(
                'success',
                "Revision version {$version->version} created successfully."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Activate Version
    |--------------------------------------------------------------------------
    */

    public function activateVersion(
        ProductionBomVersion $version
    ) {

        abort_unless(
            (int) $version->productionBom->company_id ===
            (int) auth()->user()->company_id,
            403
        );

        $this->productionBomService->activateVersion($version);

        return redirect()
            ->route(
                'production-boms.show',
                $version->production_bom_id
            )
            ->with('success', 'BOM version activated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Deactivate BOM
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $this->productionBomService->deactivateBom($bom);

        return redirect()
            ->route('production-boms.show', $bom->id)
            ->with('success', 'BOM deactivated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductionBom $bom
    ) {

        abort_unless(
            (int) $bom->company_id === (int) auth()->user()->company_id,
            403
        );

        $this->productionBomService->deleteDraft($bom);

        return redirect()
            ->route('production-boms.index')
            ->with('success', 'Draft BOM deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data Helpers
    |--------------------------------------------------------------------------
    */

    protected function getBranches()
    {
        return DB::table('branches')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'label' => $branch->code . ' - ' . $branch->name,
            ]);
    }

    private function getProductVariants(
        ?string $categoryType = null
    ) {

        $query = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('pv.is_active', true)
            ->where('p.is_active', true)
            ->where('c.is_active', true)
            ->where('p.product_type', 'product')
            ->whereNull('pv.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('c.deleted_at');

        if ($categoryType !== null) {
            $query->where('c.category_type', $categoryType);
        }

        return $query
            ->orderBy('p.name')
            ->orderBy('pv.name')
            ->get([
                'pv.id',
                'pv.sku',
                'pv.name',
                'pv.product_id',
                'p.name as product_name',
                'c.category_type',
            ])
            ->map(function ($variant) {

                $variant->label = $variant->product_name . ' - ' .
                    $variant->name .
                    ($variant->sku ? ' (' . $variant->sku . ')' : '');

                return $variant;
            });
    }

    private function getProductVariantUnits()
    {
        return DB::table('product_variant_units as pvu')
            ->join('units as u', 'u.id', '=', 'pvu.unit_id')
            ->where('pvu.is_active', true)
            ->where('u.is_active', true)
            ->whereNull('pvu.deleted_at')
            ->whereNull('u.deleted_at')
            ->orderBy('u.name')
            ->get([
                'pvu.product_variant_id',
                'pvu.unit_id',
                'u.code',
                'u.name',
                'pvu.is_base',
                'pvu.is_default',
            ])
            ->map(function ($unit) {

                $unit->label = $unit->name . ' (' . $unit->code . ')';
                $unit->value = $unit->unit_id;

                return $unit;
            });
    }

/*
|--------------------------------------------------------------------------
| Print BOM
|--------------------------------------------------------------------------
*/

public function print(
    ProductionBom $bom,
    ProductionBomVersion $version
) {

    abort_unless(
        (int) $bom->company_id === (int) auth()->user()->company_id,
        403
    );

    $version = $bom->versions()
        ->whereKey($version->id)
        ->with([
            'outputUnit',
            'operations.productionOperation',
            'materials.componentVariant.product',
            'materials.unit',
            'materials.productionBomOperation.productionOperation',
        ])
        ->firstOrFail();

    $bom->load([
        'branch',
        'productVariant.product',
    ]);

    return view('print.production.bom', [
        'title' => 'Bill of Materials',
        'bom' => $bom,
        'version' => $version,
    ]);
}
}