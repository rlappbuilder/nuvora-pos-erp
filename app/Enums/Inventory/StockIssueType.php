<?php

namespace App\Enums\Inventory;

enum StockIssueType: string
{
    case DAMAGE = 'Damage';

    case EXPIRED = 'Expired';

    case INTERNAL_USAGE = 'Internal Usage';

    case PRODUCTION = 'Production';

    case SAMPLE = 'Sample';

    case MARKETING = 'Marketing';

    case OPERATIONAL = 'Operational';

    case OTHER = 'Other';


    /*
    |--------------------------------------------------------------------------
    | Values
    |--------------------------------------------------------------------------
    */

    public static function values(): array
    {
        return array_map(
            fn (self $type) => $type->value,
            self::cases()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'label' => $type->value,
                'value' => $type->value,
            ],
            self::cases()
        );
    }
}