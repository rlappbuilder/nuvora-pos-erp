<?php

namespace App\Services;

use App\Models\Accounting\CashBank;

class CodeGeneratorService
{

    /**
     * Cash Bank
     */

    public static function cashBank(): string
    {

        $last = CashBank::withTrashed()

            ->latest('id')

            ->first();

        if (!$last) {

            return 'CB000001';

        }

        $number = (int) substr(

            $last->code,

            2

        );

        return 'CB'

            . str_pad(

                $number + 1,

                6,

                '0',

                STR_PAD_LEFT

            );

    }

}