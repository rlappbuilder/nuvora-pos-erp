<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Size;

use App\Http\Requests\SizeRequest;

use Inertia\Inertia;

class SizeController extends Controller
{
    /**
     * Display Size List
     */
    public function index()
    {
        $sizes = Size::query()

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

            ->orderBy('sort_order')

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(

            'MasterData/Sizes/Index',

            [

                'sizes' => $sizes,

                'filters' => [

                    'search' => request('search')

                ]

            ]

        );
    }

    /**
     * Store Size
     */
    public function store(
        SizeRequest $request
    )
    {
        $lastSize = Size::withTrashed()

            ->latest('id')

            ->first();

        if (!$lastSize) {

            $code = 'SIZ0001';

        } else {

            $lastNumber = (int) substr(
                $lastSize->code,
                3
            );

            $code = 'SIZ' .

                str_pad(

                    $lastNumber + 1,

                    4,

                    '0',

                    STR_PAD_LEFT

                );

        }

        Size::create([

            'code' => $code,

            'name' => $request->name,

            'sort_order' => $request->sort_order,

            'status' => $request->status,

            'created_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Size created successfully.'

            );
    }

    /**
     * Update Size
     */
    public function update(
        SizeRequest $request,
        Size $size
    )
    {
        $size->update([

            'name' => $request->name,

            'sort_order' => $request->sort_order,

            'status' => $request->status,

            'updated_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Size updated successfully.'

            );
    }

    /**
     * Delete Size
     */
    public function destroy(
        Size $size
    )
    {
        $size->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Size deleted successfully.'

            );
    }
}