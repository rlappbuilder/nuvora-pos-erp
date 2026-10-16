<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\FiscalYearController;
use App\Http\Controllers\Accounting\CashBankController;
use App\Http\Controllers\Accounting\ChartOfAccountController;
use App\Http\Controllers\Accounting\AccountingPeriodController;
use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\Accounting\GeneralLedgerController;
use App\Http\Controllers\Accounting\TrialBalanceController;
use App\Http\Controllers\Accounting\BalanceSheetController;
use App\Http\Controllers\Accounting\IncomeStatementController;
use App\Http\Controllers\Accounting\FundTransferController;
use App\Http\Controllers\Accounting\InterBranchSettlementController;
Route::middleware('auth')

    ->prefix('accounting')

    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | CASH BANK
        |--------------------------------------------------------------------------
        */
         Route::delete(
            '/cash-banks/bulk-delete',
            [CashBankController::class, 'bulkDelete']
        )->name('cash-banks.bulk-delete');


        Route::patch(
            'cash-banks/bulk-activate',
            [CashBankController::class, 'bulkActivate']
        )->name('cash-banks.bulk-activate');

        Route::patch(
            'cash-banks/bulk-deactivate',
            [CashBankController::class, 'bulkDeactivate']
        )->name('cash-banks.bulk-deactivate');

        Route::get(
            '/cash-banks/{cashBank}/export',
            [CashBankController::class, 'export']
        )->name('cash-banks.export');
        
        Route::get(
            '/cash-banks/{cashBank}/duplicate',
            [CashBankController::class, 'duplicate']
        )->name('cash-banks.duplicate');

        Route::get(
            '/cash-banks/{cashBank}/print',
            [CashBankController::class, 'print']
        )->name('cash-banks.print');

        Route::get(
            'income-statement',
            [IncomeStatementController::class, 'index']
        )->name('income-statement.index');

        Route::get(
            'accounting/income-statement/print',
            [IncomeStatementController::class, 'print']
        )->name('income-statement.print');

        Route::get(
            'trial-balance',
            [TrialBalanceController::class, 'index']
        )->name('trial-balance.index');


        Route::get('/balance-sheet/print', [
        BalanceSheetController::class,'print']
            )->name('balance-sheet.print'
            );
        Route::get(
            'balance-sheet',
            [BalanceSheetController::class, 'index']
        )->name('balance-sheet.index');
        /*
        |--------------------------------------------------------------------------
        | JOURNAL ENTRY
        |--------------------------------------------------------------------------
        */

        Route::get(
            'journal-entries',
            [JournalEntryController::class, 'index']
        )->name('journal-entries.index');


        Route::post(
            'journal-entries',
            [JournalEntryController::class, 'store']
        )->name('journal-entries.store');

        Route::put(
            'journal-entries/{journalEntry}',
            [JournalEntryController::class, 'update']
        )->name('journal-entries.update');

        Route::get(
            'journal-entries/{journalEntry}',
            [JournalEntryController::class, 'show']
        )->name('journal-entries.show');


        Route::get(
            'journal-entries/{journalEntry}/data',
            [JournalEntryController::class, 'showData']
        )->name('journal-entries.data');


        Route::post(
            'journal-entries/{journalEntry}/post',
            [JournalEntryController::class, 'post']
        )->name('journal-entries.post');
            /*
            |--------------------------------------------------------------------------
            | fiscal years
            |--------------------------------------------------------------------------
            */

            Route::get(
                'fiscal-years/create',
                [FiscalYearController::class, 'create']
            )->name('fiscal-years.create');

                            Route::get(
                            '/fiscal-years',
                            [
                                FiscalYearController::class,
                                'index',
                            ]
                        )->name('fiscal-years.index');


                        Route::post(
                            '/fiscal-years',
                            [
                                FiscalYearController::class,
                                'store',
                            ]
                        )->name('fiscal-years.store');


                        Route::get(
                            '/fiscal-years/{fiscalYear}',
                            [
                                FiscalYearController::class,
                                'show',
                            ]
                        )->name('fiscal-years.show');

                        Route::put(
                            '/fiscal-years/{fiscalYear}',
                            [
                                FiscalYearController::class,
                                'update',
                            ]
                        )->name('fiscal-years.update');
                        Route::post(
                            '/fiscal-years/{fiscalYear}/close',
                            [
                                FiscalYearController::class,
                                'close',
                            ]
                        )->name('fiscal-years.close');


                        Route::post(
                            '/fiscal-years/{fiscalYear}/reopen',
                            [
                                FiscalYearController::class,
                                'reopen',
                            ]
                        )->name('fiscal-years.reopen');

                        /*
                |---------------------------------------------------------------
                | FUND TRANSFER
                |---------------------------------------------------------------
                */

                Route::get(
                    'fund-transfers',
                    [FundTransferController::class, 'index']
                )->name('fund-transfers.index');

                Route::get(
                    'fund-transfers/create',
                    [FundTransferController::class, 'create']
                )->name('fund-transfers.create');

                Route::post(
                    'fund-transfers',
                    [FundTransferController::class, 'store']
                )->name('fund-transfers.store');

                Route::get(
                    'fund-transfers/accounts',
                    [FundTransferController::class, 'accounts']
                )->name('fund-transfers.accounts');

                Route::get(
                    'fund-transfers/{fundTransfer}/data',
                    [FundTransferController::class, 'showData']
                )->name('fund-transfers.data');

                Route::get(
                    'fund-transfers/{fundTransfer}',
                    [FundTransferController::class, 'show']
                )->name('fund-transfers.show');

                Route::post(
                    'fund-transfers/{fundTransfer}/submit',
                    [FundTransferController::class, 'submit']
                )->name('fund-transfers.submit');

                Route::post(
                    'fund-transfers/{fundTransfer}/approve',
                    [FundTransferController::class, 'approve']
                )->name('fund-transfers.approve');

                Route::post(
                    'fund-transfers/{fundTransfer}/reject',
                    [FundTransferController::class, 'reject']
                )->name('fund-transfers.reject');

                Route::post(
                    'fund-transfers/{fundTransfer}/post',
                    [FundTransferController::class, 'post']
                )->name('fund-transfers.post');

                Route::post(
                    'fund-transfers/{fundTransfer}/cancel',
                    [FundTransferController::class, 'cancel']
                )->name('fund-transfers.cancel');

            /*
            |--------------------------------------------------------------------------
            | INTER-BRANCH SETTLEMENT
            |--------------------------------------------------------------------------
            */

            // Index
            Route::get(
                'inter-branch-settlements',
                [InterBranchSettlementController::class, 'index']
            )->name('inter-branch-settlements.index');

            // Create
            Route::get(
                'inter-branch-settlements/create',
                [InterBranchSettlementController::class, 'create']
            )->name('inter-branch-settlements.create');

            // Store
            Route::post(
                'inter-branch-settlements',
                [InterBranchSettlementController::class, 'store']
            )->name('inter-branch-settlements.store');

            // Eligible Fund Transfers
            // Harus diletakkan sebelum route {settlement}.
            Route::get(
                'inter-branch-settlements/eligible-transfers',
                [InterBranchSettlementController::class, 'eligibleTransfers']
            )->name('inter-branch-settlements.eligible-transfers');
            // Accounts berdasarkan cabang
            Route::get(
                'inter-branch-settlements/accounts',
                [InterBranchSettlementController::class, 'accounts']
            )->name('inter-branch-settlements.accounts');
            // Detail data untuk modal
            Route::get(
                'inter-branch-settlements/{settlement}/data',
                [InterBranchSettlementController::class, 'showData']
            )->name('inter-branch-settlements.data');

            // Detail settlement
            Route::get(
                'inter-branch-settlements/{settlement}',
                [InterBranchSettlementController::class, 'show']
            )->name('inter-branch-settlements.show');

            // Update
            Route::put(
                'inter-branch-settlements/{settlement}',
                [InterBranchSettlementController::class, 'update']
            )->name('inter-branch-settlements.update');

            // Submit
            Route::post(
                'inter-branch-settlements/{settlement}/submit',
                [InterBranchSettlementController::class, 'submit']
            )->name('inter-branch-settlements.submit');

            // Approve
            Route::post(
                'inter-branch-settlements/{settlement}/approve',
                [InterBranchSettlementController::class, 'approve']
            )->name('inter-branch-settlements.approve');

            // Reject
            Route::post(
                'inter-branch-settlements/{settlement}/reject',
                [InterBranchSettlementController::class, 'reject']
            )->name('inter-branch-settlements.reject');

            // Post payment
            Route::post(
                'inter-branch-settlements/{settlement}/post',
                [InterBranchSettlementController::class, 'post']
            )->name('inter-branch-settlements.post');

            // Receive payment
            Route::post(
                'inter-branch-settlements/{settlement}/receive',
                [InterBranchSettlementController::class, 'receive']
            )->name('inter-branch-settlements.receive');

            // Cancel
            Route::post(
                'inter-branch-settlements/{settlement}/cancel',
                [InterBranchSettlementController::class, 'cancel']
            )->name('inter-branch-settlements.cancel');

            // Delete
            Route::delete(
                'inter-branch-settlements/{settlement}',
                [InterBranchSettlementController::class, 'destroy']
            )->name('inter-branch-settlements.destroy');
                Route::get(
                '/inter-branch-settlements/{settlement}/print',
                [InterBranchSettlementController::class, 'print']
            )->name('inter-branch-settlements.print');
             /*
            |--------------------------------------------------------------------------
            | accounting period
            |--------------------------------------------------------------------------
            */

                   Route::get(
                    'accounting-periods',
                    [AccountingPeriodController::class, 'index']
                )->name('accounting-periods.index');

                Route::get(
                    'accounting-periods/{accountingPeriod}',
                    [AccountingPeriodController::class, 'show']
                )->name('accounting-periods.show');

                Route::post(
                    'accounting-periods/{accountingPeriod}/close',
                    [AccountingPeriodController::class, 'close']
                )->name('accounting-periods.close');

                Route::post(
                    'accounting-periods/{accountingPeriod}/reopen',
                    [AccountingPeriodController::class, 'reopen']
                )->name('accounting-periods.reopen');

               /*
                |--------------------------------------------------------------------------
                | GENERAL LEDGER
                |--------------------------------------------------------------------------
                */

                Route::get(
                    'general-ledgers',
                    [GeneralLedgerController::class, 'index']
                )->name('general-ledgers.index');

        /*
        |--------------------------------------------------------------------------
        | Accounting Journal
        |--------------------------------------------------------------------------
        */

                Route::resource(
                    'accounting-journals',
                    \App\Http\Controllers\Accounting\AccountingJournalController::class
                );

            
                /*
            |--------------------------------------------------------------------------
            | CHART OF ACCOUNTS
            |--------------------------------------------------------------------------
            */

          Route::post(
                'chart-of-accounts/bulk-delete',
                [ChartOfAccountController::class, 'bulkDelete']
            )->name('chart-of-accounts.bulk-delete');

            Route::post(
                'chart-of-accounts/bulk-activate',
                [ChartOfAccountController::class, 'bulkActivate']
            )->name('chart-of-accounts.bulk-activate');

            Route::post(
                'chart-of-accounts/bulk-deactivate',
                [ChartOfAccountController::class, 'bulkDeactivate']
            )->name('chart-of-accounts.bulk-deactivate');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/export',
                [ChartOfAccountController::class, 'export']
            )->name('chart-of-accounts.export');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/duplicate',
                [ChartOfAccountController::class, 'duplicate'
            ])->name('chart-of-accounts.duplicate');

            Route::get(
                '/chart-of-accounts/{chartOfAccount}/print',
                [ChartOfAccountController::class, 'print']
            )->name('chart-of-accounts.print');

             Route::get(
                'chart-of-accounts/preview-code',
                [ChartOfAccountController::class, 'previewCode']
            )->name('chart-of-accounts.preview-code');

             Route::resource(
                    'chart-of-accounts',
                    ChartOfAccountController::class
                );

         

        Route::resource(

            'cash-banks',

            CashBankController::class

        );

       

    });