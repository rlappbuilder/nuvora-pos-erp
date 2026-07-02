<?php

namespace App\Models\Accounting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
class CashBank extends Model
{
    use HasFactory;
     use SoftDeletes;

protected $fillable = [

    'company_id',

    'branch_id',

    'coa_id',

    'code',

    'name',

    'type',

    'bank_name',

    'bank_branch',

    'account_number',

    'account_holder',

    'opening_balance',

    'current_balance',

    'description',

    'status',

    'created_by',

    'updated_by',

];

    protected $casts = [

    'opening_balance' => 'decimal:2',

    'current_balance' => 'decimal:2',

    'status' => 'boolean',

];
    public function creator()
    {
        return $this->belongsTo(

            User::class,

            'created_by'

        );
    }

    public function updater()
    {
        return $this->belongsTo(

            User::class,

            'updated_by'

        );
    }

    public function supplierPayments()
    {
        return $this->hasMany(

            SupplierPayment::class,

            'cash_bank_id'

        );
    }
    public function company()
{
    return $this->belongsTo(

        Company::class

    );
}

public function branch()
{
    return $this->belongsTo(

        Branch::class

    );
}
}