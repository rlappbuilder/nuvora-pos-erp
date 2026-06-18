<?php

namespace App\Http\Controllers\MasterData;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $customers = Customer::latest()

        ->paginate(10);

    return Inertia::render(

        'MasterData/Customers/Index',

        [

            'customers' => $customers

        ]

    );
}

    /**
     * Show the form for creating a new resource.
     */
public function create()
{
    return Inertia::render(

        'MasterData/Customers/Create'

    );
}

    /**
     * Store a newly created resource in storage.
     */
   public function store(
    Request $request
)
{
    $validated = $request->validate([

        

        'name' => [
            'required',
            'max:255'
        ],

        'contact_person' => [
            'nullable',
            'max:255'
        ],
        'credit_limit' => [
            'nullable',
            'numeric',
            'min:0'
        ],
        'phone' => [
            'nullable',
            'max:50'
        ],

        'email' => [
            'nullable',
            'email'
        ],

        'city' => [
            'nullable',
            'max:100'
        ],

        'tax_number' => [
            'nullable',
            'max:100'
        ],

        'payment_term' => [
            'nullable',
            'integer'
        ],

        'address' => [
            'nullable'
        ],

        'status' => [
            'boolean'
        ],

    ]);

   

$lastCustomer = Customer::latest(
    'id'
)->first();

$nextNumber = $lastCustomer
    ? $lastCustomer->id + 1
    : 1;

$validated['customer_code'] =
    'CUS' .
    str_pad(
        $nextNumber,
        5,
        '0',
        STR_PAD_LEFT
    );
 $validated['created_by'] =
        auth()->id();
    Customer::create(
        $validated
    );

    return redirect()

        ->route(
            'customers.index'
        )

        ->with(

            'success',

            'Customers created successfully.'

        );
}

    /**
     * Display the specified resource.
     */
    public function show(
    Customer $customer
)
{
    return Inertia::render(

        'MasterData/Customers/Show',

        [

            'customer' => $customer

        ]

    );
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
 public function update(

    Request $request,

    Customer $customer

)
{
    $validated = $request->validate([

        'name' => 'required',

        'contact_person' => 'nullable',

        'phone' => 'nullable',

        'email' => 'nullable|email',

        'city' => 'nullable',

        'tax_number' => 'nullable',

        'payment_term' => 'nullable|integer',

        'credit_limit' => 'nullable|numeric',

        'address' => 'nullable',

        'status' => 'boolean',

    ]);

    $validated['updated_by'] =
        auth()->id();

    $customer->update(
        $validated
    );

    return redirect()

        ->route(
            'customers.index'
        )

        ->with(

            'success',

            'Customer updated successfully.'

        );
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
    Customer $customer
)
{
    $customer->delete();

    return redirect()

        ->route(
            'customers.index'
        )

        ->with(

            'success',

            'Customer deleted successfully.'

        );
}
}
