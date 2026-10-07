<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CashierDepositRequest;
use App\Http\Requests\POS\RejectCashierDepositRequest;
use App\Http\Requests\POS\CancelCashierDepositRequest;
use App\Models\POS\CashierDeposit;
use App\Services\POS\CashierDepositService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashierDepositController extends Controller
{
    protected CashierDepositService $cashierDepositService;

    public function __construct(
        CashierDepositService $cashierDepositService
    ) {
        $this->cashierDepositService = $cashierDepositService;
    }

    public function store(CashierDepositRequest $request)
    {
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

    public function submit(
        Request $request,
        CashierDeposit $cashierDeposit
    ) {
        $this->cashierDepositService->submit(
            $request->user(),
            $cashierDeposit
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit submitted successfully.'
            );
    }

    public function approve(
        Request $request,
        CashierDeposit $cashierDeposit
    ) {
        $this->cashierDepositService->approve(
            $request->user(),
            $cashierDeposit
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit approved successfully.'
            );
    }

    public function reject(
        RejectCashierDepositRequest $request,
        CashierDeposit $cashierDeposit
    ) {
        $data = $request->validated();

        $this->cashierDepositService->reject(
            $request->user(),
            $cashierDeposit,
            $data['reason']
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit rejected successfully.'
            );
    }

    public function receive(
        Request $request,
        CashierDeposit $cashierDeposit
    ) {
        $this->cashierDepositService->receive(
            $request->user(),
            $cashierDeposit
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit received successfully.'
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

    public function cancel(
        CancelCashierDepositRequest $request,
        CashierDeposit $cashierDeposit
    ) {
        $data = $request->validated();

        $this->cashierDepositService->cancel(
            $request->user(),
            $cashierDeposit,
            $data['reason']
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cash deposit cancelled successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        CashierDeposit $cashierDeposit
    ) {
        $cashierDeposit->load([
            'destinationAccount',
            'cashierSession',
            'activities',
        ]);

        return Inertia::render(
            'POS/Cashier/Deposit/Print',
            [
                'deposit' => $cashierDeposit,
            ]
        );
    }
}