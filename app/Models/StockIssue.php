<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIssue extends Model
{
    protected $fillable = [

        'issue_number',

        'issue_date',

        'warehouse_id',

        'issue_type',

        'reference_number',

        'remarks',

        'total_qty',

        'total_cost',

        'status',

        'posted_by',

        'posted_at',

        'completed_by',

        'completed_at',

        'cancelled_by',

        'cancelled_at',

        'cancel_reason',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'issue_date' => 'date',

        'posted_at' => 'datetime',

        'completed_at' => 'datetime',

        'cancelled_at' => 'datetime',

    ];

    public function warehouse()
    {
        return $this->belongsTo(

            Warehouse::class

        );
    }

    public function details()
    {
        return $this->hasMany(

            StockIssueDetail::class

        );
    }

    public function creator()
    {
        return $this->belongsTo(

            User::class,

            'created_by'

        );
    }

    public function poster()
    {
        return $this->belongsTo(

            User::class,

            'posted_by'

        );
    }

    public function completer()
    {
        return $this->belongsTo(

            User::class,

            'completed_by'

        );
    }

    public function canceller()
    {
        return $this->belongsTo(

            User::class,

            'cancelled_by'

        );
    }
    public static function generateNumber()
{
    $last = self::latest('id')->first();

    $number = $last
        ? ((int) substr($last->issue_number, 3)) + 1
        : 1;

    return 'ISS' . str_pad(

        $number,

        6,

        '0',

        STR_PAD_LEFT

    );
}
}