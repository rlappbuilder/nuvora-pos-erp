<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Category;
use App\Http\Requests\CategoryRequest;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display Category List
     */
   public function index()
{
    $categories = Category::query()

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
        'MasterData/Categories/Index',
        [
            'categories' => $categories,

            'filters' => [
                'search' => request('search')
            ]
        ]
    );
}

    /**
     * Store Category
     */
   public function store(CategoryRequest $request)
{
    $lastCategory = Category::withTrashed()

    ->latest('id')

    ->first();

    

    if (!$lastCategory) {

        $code = 'CAT0001';

    } else {

        $lastNumber = (int) substr(
            $lastCategory->code,
            3
        );

        $code = 'CAT' .
            str_pad(
                $lastNumber + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

    }

    Category::create([

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
            'Category created successfully.'
        );
}
    /**
     * Update Category
     */
    public function update(
    CategoryRequest $request,
    Category $category
)
{
    $category->update([

        'name' => $request->name,

        'description' => $request->description,

        'status' => $request->status,

        'updated_by' => auth()->id(),

    ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Category updated successfully.'
        );
}
    /**
     * Delete Category
     */
   public function destroy(
    Category $category
)
{
    $category->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Category deleted successfully.'
        );
}
public function products()
{
    return $this->hasMany(
        Product::class
    );
}
}