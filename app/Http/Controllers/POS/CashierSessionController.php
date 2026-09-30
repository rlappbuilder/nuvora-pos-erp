<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CashierSessionRequest;
use App\Services\POS\CashierSessionService;
use Illuminate\Http\Request;
use App\Models\POS\CashierSession;
use Inertia\Inertia;
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

    /*
|--------------------------------------------------------------------------
| Print Close Session Report
|--------------------------------------------------------------------------
*/

public function print(
    Request $request,
    CashierSession $session
) {
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Validate Session Access
    |--------------------------------------------------------------------------
    */

    if (
        (int) $session->company_id !==
        (int) $user->company_id
    ) {
        abort(403);
    }

    if (
        (int) $session->user_id !==
        (int) $user->id
    ) {
        abort(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Session Must Be Closed
    |--------------------------------------------------------------------------
    */

    if ($session->status !== 'closed') {
        abort(422, 'Cashier session belum ditutup.');
    }

    /*
    |--------------------------------------------------------------------------
    | Report Data
    |--------------------------------------------------------------------------
    */

    $summary =
        $this->cashierSessionService
            ->getCloseSummary($session);

    /*
    |--------------------------------------------------------------------------
    | Reconciliation
    |--------------------------------------------------------------------------
    */

    $actualCash =
        (float) (
            $session->closing_balance ?? 0
        );

    $expectedCash =
        (float) (
            $summary['cash_movement']['expected_cash']
            ?? 0
        );

    $difference =
        $actualCash - $expectedCash;

    $reconciliationStatus =
        $difference === 0.0
            ? 'Balanced'
            : (
                $difference < 0
                    ? 'Cash Short'
                    : 'Cash Over'
            );

    return view(
        'print.pos.cashier.close-session-report',
        [
            'session' =>
                $session->fresh([
                    'company',
                    'branch',
                    'warehouse',
                    'user',
                    'cashAccount',
                    'previousSession',
                ]),

            'summary' =>
                $summary,

            'actualCash' =>
                $actualCash,

            'expectedCash' =>
                $expectedCash,

            'difference' =>
                $difference,

            'reconciliationStatus' =>
                $reconciliationStatus,
        ]
    );
}
}