<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Http\Requests\ColorRequest;
use Inertia\Inertia;

class ColorController extends Controller
{
    /**
     * Display Color List
     */
    public function index()
    {
        $colors = Color::query()

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

            'MasterData/Colors/Index',

            [

                'colors' => $colors,

                'filters' => [

                    'search' => request('search')

                ]

            ]

        );
    }

    /**
     * Store Color
     */
    public function store(
        ColorRequest $request
    )
    {
        $lastColor = Color::withTrashed()

            ->latest('id')

            ->first();

        if (!$lastColor) {

            $code = 'CLR0001';

        } else {

            $lastNumber = (int) substr(
                $lastColor->code,
                3
            );

            $code = 'CLR' .

                str_pad(

                    $lastNumber + 1,

                    4,

                    '0',

                    STR_PAD_LEFT

                );

        }

        Color::create([

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

                'Color created successfully.'

            );
    }

    /**
     * Update Color
     */
    public function update(
        ColorRequest $request,
        Color $color
    )
    {
        $color->update([

            'name' => $request->name,

            'description' => $request->description,

            'status' => $request->status,

            'updated_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Color updated successfully.'

            );
    }

    /**
     * Delete Color
     */
    public function destroy(
        Color $color
    )
    {
        $color->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Color deleted successfully.'

            );
    }
}