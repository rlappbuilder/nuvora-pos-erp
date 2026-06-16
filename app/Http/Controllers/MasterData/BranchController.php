<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Company;
use App\Http\Requests\BranchRequest;
use Inertia\Inertia;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $branches = Branch::with('company')

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

        'MasterData/Branches/Index',

        [

            'branches' => $branches,

            'filters' => [

                'search' => request('search')

            ]

        ]

    );
}

    /**
     * Show the form for creating a new resource.
     */
   public function create()
{
    return Inertia::render(

        'MasterData/Branches/Create',

        [

            'companies' => Company::where(

                'status',

                true

            )->get()

        ]

    );
}

    /**
     * Store a newly created resource in storage.
     */
   public function store(
    BranchRequest $request
)
{
    $lastBranch = Branch::withTrashed()

        ->latest('id')

        ->first();

    if (!$lastBranch) {

        $code = 'BR0001';

    } else {

        $lastNumber = (int) substr(

            $lastBranch->code,

            2

        );

        $code = 'BR' .

            str_pad(

                $lastNumber + 1,

                4,

                '0',

                STR_PAD_LEFT

            );

    }

    Branch::create([

        'company_id' => $request->company_id,

        'code' => $code,

        'name' => $request->name,

        'manager_name' => $request->manager_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'address' => $request->address,

        'city' => $request->city,

        'province' => $request->province,

        'is_head_office' => $request->is_head_office,

        'status' => $request->status,

        'created_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'branches.index'

        )

        ->with(

            'success',

            'Branch created successfully.'

        );
}

    /**
     * Display the specified resource.
     */
    public function show(
    Branch $branch
)
{
    $branch->load(
        'company'
    );

    return Inertia::render(

        'MasterData/Branches/Show',

        [

            'branch' => $branch

        ]

    );
}

    /**
     * Show the form for editing the specified resource.
     */
   public function edit(
    Branch $branch
)
{
    return Inertia::render(

        'MasterData/Branches/Edit',

        [

            'branch' => $branch,

            'companies' => Company::where(

                'status',

                true

            )->get()

        ]

    );
}
    /**
     * Update the specified resource in storage.
     */
   public function update(
    BranchRequest $request,
    Branch $branch
)
{
    $branch->update([

        'company_id' => $request->company_id,

        'name' => $request->name,

        'manager_name' => $request->manager_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'address' => $request->address,

        'city' => $request->city,

        'province' => $request->province,

        'is_head_office' => $request->is_head_office,

        'status' => $request->status,

        'updated_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'branches.show',

            $branch->id

        )

        ->with(

            'success',

            'Branch updated successfully.'

        );
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
    Branch $branch
)
{
    $branch->delete();

    return redirect()

        ->route(

            'branches.index'

        )

        ->with(

            'success',

            'Branch deleted successfully.'

        );
}
}
