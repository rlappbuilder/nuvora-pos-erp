<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CashierDepositRequest;
use App\Models\POS\CashierDeposit;
use App\Services\POS\CashierDepositService;
use Illuminate\Http\Request;

class CashierDepositController extends Controller
{
    protected CashierDepositService $cashierDepositService;

    public function __construct(
        CashierDepositService $cashierDepositService
    ) {
        $this->cashierDepositService =
            $cashierDepositService;
    }

    public function store(
        CashierDepositRequest $request
    ) {
        $data = $request->validated();

        $this->cashierDepositService->create(
            $request->user(),
            (int) $data['destination_account_id'],
            (float) $data['amount'],
            $data['deposited_at'],
            $data['note'] ?? null
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit created successfully.'
            );
    }

    public function post(
        Request $request,
        CashierDeposit $cashierDeposit
    ) {
        $this->cashierDepositService->post(
            $request->user(),
            $cashierDeposit
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit posted successfully.'
            );
    }
}