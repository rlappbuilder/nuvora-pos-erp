<?php

namespace Database\Seeders\MasterData;

use Illuminate\Database\Seeder;
use App\Models\MasterData\PaymentTerm;

class PaymentTermSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTerms = [

            [
                'code' =>
                    'CASH',

                'name' =>
                    'Cash',

                'days' =>
                    0,

                'description' =>
                    'Payment due immediately.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET7',

                'name' =>
                    'Net 7 Days',

                'days' =>
                    7,

                'description' =>
                    'Payment due within 7 days.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET14',

                'name' =>
                    'Net 14 Days',

                'days' =>
                    14,

                'description' =>
                    'Payment due within 14 days.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET30',

                'name' =>
                    'Net 30 Days',

                'days' =>
                    30,

                'description' =>
                    'Payment due within 30 days.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET45',

                'name' =>
                    'Net 45 Days',

                'days' =>
                    45,

                'description' =>
                    'Payment due within 45 days.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET60',

                'name' =>
                    'Net 60 Days',

                'days' =>
                    60,

                'description' =>
                    'Payment due within 60 days.',

                'status' =>
                    true,
            ],

            [
                'code' =>
                    'NET90',

                'name' =>
                    'Net 90 Days',

                'days' =>
                    90,

                'description' =>
                    'Payment due within 90 days.',

                'status' =>
                    true,
            ],

        ];


        foreach (
            $paymentTerms as $paymentTerm
        ) {

            PaymentTerm::updateOrCreate(

                [
                    'code' =>
                        $paymentTerm['code'],
                ],

                $paymentTerm

            );

        }
    }
}