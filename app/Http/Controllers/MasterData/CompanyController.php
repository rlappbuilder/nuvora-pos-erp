<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\Company;

use App\Http\Requests\CompanyRequest;

use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display Company List
     */
    public function index()
    {
        $companies = Company::query()

            ->when(
                request('search'),
                function ($query) {

                    $query->where(
                        'company_code',
                        'like',
                        '%' . request('search') . '%'
                    )

                    ->orWhere(
                        'company_name',
                        'like',
                        '%' . request('search') . '%'
                    )

                    ->orWhere(
                        'legal_name',
                        'like',
                        '%' . request('search') . '%'
                    )

                    ->orWhere(
                        'phone',
                        'like',
                        '%' . request('search') . '%'
                    )

                    ->orWhere(
                        'email',
                        'like',
                        '%' . request('search') . '%'
                    );

                }
            )

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(

            'MasterData/Companies/Index',

            [

                'companies' => $companies,

                'filters' => [

                    'search' => request('search')

                ]

            ]

        );
    }

    /**
 * Create Company
 */
public function create()
{
    return Inertia::render(

        'MasterData/Companies/Create'

    );
}
    /**
     * Store Company
     */
    /**
 * Show Company
 */

    public function store(
    CompanyRequest $request
)
{
    $lastCompany = Company::withTrashed()

        ->latest('id')

        ->first();

    if (!$lastCompany) {

        $code = 'CMP0001';

    } else {

        $lastNumber = (int) substr(

            $lastCompany->company_code,

            3

        );

        $code = 'CMP' .

            str_pad(

                $lastNumber + 1,

                4,

                '0',

                STR_PAD_LEFT

            );

    }

    /*
    |--------------------------------------------------------------------------
    | Upload Logo
    |--------------------------------------------------------------------------
    */

    $logo = null;

    if (

        $request->hasFile(
            'logo'
        )

    ) {

        $logo =

            $request

            ->file(
                'logo'
            )

            ->store(

                'companies',

                'public'

            );

    }

    /*
    |--------------------------------------------------------------------------
    | Save Company
    |--------------------------------------------------------------------------
    */

    Company::create([

        'company_code' => $code,

        'company_name' => $request->company_name,

        'legal_name' => $request->legal_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'website' => $request->website,

        'tax_number' => $request->tax_number,

        'director_name' => $request->director_name,

        'logo' => $logo,

        'address' => $request->address,

        'city' => $request->city,

        'province' => $request->province,

        'postal_code' => $request->postal_code,

        'status' => $request->status,

        'created_by' => auth()->id(),

    ]);

    return redirect()

        ->route(
            'companies.index'
        )

        ->with(

            'success',

            'Company created successfully.'

        );
}
public function show(
    Company $company
)
{
    return Inertia::render(

        'MasterData/Companies/Show',

        [

            'company' => $company

        ]

    );
}

/**
 * Edit Company
 */
public function edit(
    Company $company
)
{
    return Inertia::render(

        'MasterData/Companies/Edit',

        [

            'company' => $company

        ]

    );
}
    /**
     * Update Company
     */
    public function update(
    CompanyRequest $request,
    Company $company
)
{
    $logo = $company->logo;

    if (

        $request->hasFile(
            'logo'
        )

    ) {

        $logo =

            $request

            ->file(
                'logo'
            )

            ->store(

                'companies',

                'public'

            );

    }

    $company->update([

        'company_name' => $request->company_name,

        'legal_name' => $request->legal_name,

        'phone' => $request->phone,

        'email' => $request->email,

        'website' => $request->website,

        'tax_number' => $request->tax_number,

        'director_name' => $request->director_name,

        'logo' => $logo,

        'address' => $request->address,

        'city' => $request->city,

        'province' => $request->province,

        'postal_code' => $request->postal_code,

        'status' => $request->status,

        'updated_by' => auth()->id(),

    ]);

    return redirect()

        ->route(

            'companies.show',

            $company->id

        )

        ->with(

            'success',

            'Company updated successfully.'

        );
}

    /**
     * Delete Company
     */
    public function destroy(
        Company $company
    )
    {
        $company->delete();

        return redirect()

            ->back()

            ->with(

                'success',

                'Company deleted successfully.'

            );
    }
}