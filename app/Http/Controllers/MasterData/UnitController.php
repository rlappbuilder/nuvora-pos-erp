<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Unit;

use App\Http\Requests\UnitRequest;

use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display Unit List
     */
    public function index()
    {
        $units = Unit::query()

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

            'MasterData/Units/Index',

            [

                'units' => $units,

                'filters' => [

                    'search' => request('search')

                ]

            ]

        );
    }

    /**
     * Store Unit
     */
    public function store(
        UnitRequest $request
    )
    {
        $lastUnit = Unit::withTrashed()

            ->latest('id')

            ->first();

        if (!$lastUnit) {

            $code = 'UNT0001';

        } else {

            $lastNumber = (int) substr(
                $lastUnit->code,
                3
            );

            $code = 'UNT' .

                str_pad(

                    $lastNumber + 1,

                    4,

                    '0',

                    STR_PAD_LEFT

                );

        }

        Unit::create([

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

                'Unit created successfully.'

            );
    }

    /**
     * Update Unit
     */
    public function update(
        UnitRequest $request,
        Unit $unit
    )
    {
        $unit->update([

            'name' => $request->name,

            'description' => $request->description,

            'status' => $request->status,

            'updated_by' => auth()->id(),

        ]);

        return redirect()

            ->back()

            ->with(

                'success',

                'Unit updated successfully.'

            );
    }

    /**
     * Delete Unit
     */
    public function destroy(
        Unit $unit
    )
    {
        $unit->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Unit deleted successfully.'

            );
    }
}