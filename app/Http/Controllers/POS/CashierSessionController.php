<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CashierSessionRequest;
use App\Services\POS\CashierSessionService;
use Illuminate\Http\Request;

class CashierSessionController extends Controller
{
    protected CashierSessionService $cashierSessionService;

    public function __construct(
        CashierSessionService $cashierSessionService
    ) {
        $this->cashierSessionService =
            $cashierSessionService;
    }

    /*
    |--------------------------------------------------------------------------
    | Open Session
    |--------------------------------------------------------------------------
    */

    public function store(
        CashierSessionRequest $request
    ) {
        $data = $request->validated();

        $this->cashierSessionService->open(
            $request->user(),
            (int) $data['warehouse_id'],
            (int) $data['cash_account_id'],
            (float) $data['opening_balance']
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cashier session opened successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Close Session
    |--------------------------------------------------------------------------
    */

    public function close(
        Request $request
    ) {
        $request->validate([
            'closing_balance' => [
                'required',
                'numeric',
                'min:0',
            ],

            'closing_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $user =
            $request->user();

        $session =
            $this->cashierSessionService
                ->getActiveSession($user);

        if (! $session) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'No active cashier session found.'
                );
        }

        $this->cashierSessionService->close(
            $session,
            (float) $request->closing_balance,
            $request->closing_note
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Cashier session closed successfully.'
            );
    }
}