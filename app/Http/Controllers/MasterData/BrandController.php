<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\MasterData\Brand;

use App\Http\Requests\BrandRequest;

use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display Brand List
     */
    public function index()
    {
        $brands = Brand::query()

            ->when(
                request('search'),
                function ($query) {

                    $query->where(
                        'code',
                        'like',
                        '%' . request('search') . '%'
                    )

                    ->orWhere(
                        'name',
                        'like',
                        '%' . request('search') . '%'
                    );

                }
            )

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(

            'MasterData/Brands/Index',

            [

                'brands' => $brands,

                'filters' => [

                    'search' => request('search')

                ]

            ]

        );
    }

    /**
     * Store Brand
     */
    public function store(
        BrandRequest $request
    )
    {
        $lastBrand = Brand::withTrashed()

            ->latest('id')

            ->first();

        if (!$lastBrand) {

            $code = 'BRD0001';

        } else {

            $lastNumber = (int) substr(
                $lastBrand->code,
                3
            );

            $code = 'BRD' .

                str_pad(

                    $lastNumber + 1,

                    4,

                    '0',

                    STR_PAD_LEFT

                );

        }

        Brand::create([

            'code' => $code,

            'name' => $request->name,

            'description' => $request->description,

            'status' => $request->status,

            'created_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Brand created successfully.'

            );
    }

    /**
     * Update Brand
     */
    public function update(
        BrandRequest $request,
        Brand $brand
    )
    {
        $brand->update([

            'name' => $request->name,

            'description' => $request->description,

            'status' => $request->status,

            'updated_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Brand updated successfully.'

            );
    }

    /**
     * Delete Brand
     */
    public function destroy(
        Brand $brand
    )
    {
        $brand->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Brand deleted successfully.'

            );
    }

    public function products()
{
    return $this->hasMany(
        Product::class
    );
}
}