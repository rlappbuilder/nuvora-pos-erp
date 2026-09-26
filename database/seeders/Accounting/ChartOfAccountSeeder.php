<?php

namespace Database\Seeders\Accounting;

use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 2;

        $accounts = [

            // ==========================================================
            // CURRENT ASSETS
            // ==========================================================

            [
                'code' => '110100',
                'name' => 'Cash & Bank',
                'category' => 'Cash & Bank',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '110101',
                'name' => 'Cash On Hand',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110102',
                'name' => 'Bank',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110103',
                'name' => 'Bank BRI - Reno Sri Wahyuni',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110104',
                'name' => 'Petty Cash',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110105',
                'name' => 'Bank - BCA',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110106',
                'name' => 'Bank - Mandiri',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],
            [
                'code' => '110107',
                'name' => 'Consignment Cash',
                'category' => 'Cash & Bank',
                'parent' => '110100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
            'code' => '110108',
            'name' => 'COH Lbb Store',
            'category' => 'Cash & Bank',
            'parent' => '110100',
            'normal_balance' => 'Debit',
            'is_header' => true,
            'is_posting' => false,
        ],

        [
            'code' => '110109',
            'name' => 'COH Lbb Konv',
            'category' => 'Cash & Bank',
            'parent' => '110100',
            'normal_balance' => 'Debit',
            'is_header' => true,
            'is_posting' => false,
        ],

        [
            'code' => '110110',
            'name' => 'COH Cab.Ps. Raya',
            'category' => 'Cash & Bank',
            'parent' => '110100',
            'normal_balance' => 'Debit',
            'is_header' => true,
            'is_posting' => false,
        ],

        [
            'code' => '110111',
            'name' => 'Cashier Drawer - Lbb Store',
            'category' => 'Cash & Bank',
            'parent' => '110108',
            'normal_balance' => 'Debit',
            'is_header' => false,
            'is_posting' => true,
        ],

        [
            'code' => '110112',
            'name' => 'Cashier Drawer - Lbb Konv',
            'category' => 'Cash & Bank',
            'parent' => '110109',
            'normal_balance' => 'Debit',
            'is_header' => false,
            'is_posting' => true,
        ],

        [
            'code' => '110113',
            'name' => 'Cashier Drawer - Cab.Ps. Raya',
            'category' => 'Cash & Bank',
            'parent' => '110110',
            'normal_balance' => 'Debit',
            'is_header' => false,
            'is_posting' => true,
        ],

            // ----------------------------------------------------------
            // Accounts Receivable
            // ----------------------------------------------------------

            [
                'code' => '110200',
                'name' => 'Accounts Receivable',
                'category' => 'Accounts Receivable',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '110201',
                'name' => 'Trade Receivable',
                'category' => 'Accounts Receivable',
                'parent' => '110200',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110202',
                'name' => 'Employee Receivable',
                'category' => 'Accounts Receivable',
                'parent' => '110200',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110203',
                'name' => 'Other Receivable',
                'category' => 'Accounts Receivable',
                'parent' => '110200',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110204',
                'name' => 'Allowance for Doubtful Accounts',
                'category' => 'Accounts Receivable',
                'parent' => '110200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ----------------------------------------------------------
            // Inventory
            // ----------------------------------------------------------

            [
                'code' => '110300',
                'name' => 'Inventory',
                'category' => 'Inventory',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '110301',
                'name' => 'Merchandise Inventory',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110302',
                'name' => 'Raw Material Inventory',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110303',
                'name' => 'Finished Goods',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110304',
                'name' => 'Inventory In Transit',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110305',
                'name' => 'Inventory Adjustment',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110306',
                'name' => 'Inventory Provision',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

             [
                'code' => '110307',
                'name' => 'Inventory - Consignment',
                'category' => 'Inventory',
                'parent' => '110300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],
            // ----------------------------------------------------------
            // Prepaid Expense
            // ----------------------------------------------------------

            [
                'code' => '110400',
                'name' => 'Prepaid Expense',
                'category' => 'Prepaid Expense',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '110401',
                'name' => 'Prepaid Rent',
                'category' => 'Prepaid Expense',
                'parent' => '110400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110402',
                'name' => 'Prepaid Insurance',
                'category' => 'Prepaid Expense',
                'parent' => '110400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110403',
                'name' => 'Prepaid Tax',
                'category' => 'Prepaid Expense',
                'parent' => '110400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '110404',
                'name' => 'Other Prepaid Expense',
                'category' => 'Prepaid Expense',
                'parent' => '110400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // FIXED ASSETS
            // ==========================================================

            [
                'code' => '120100',
                'name' => 'Land',
                'category' => 'Land',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '120101',
                'name' => 'Land',
                'category' => 'Land',
                'parent' => '120100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120200',
                'name' => 'Building',
                'category' => 'Building',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '120201',
                'name' => 'Building',
                'category' => 'Building',
                'parent' => '120200',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120202',
                'name' => 'Accumulated Depreciation - Building',
                'category' => 'Building',
                'parent' => '120200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120300',
                'name' => 'Vehicle',
                'category' => 'Vehicle',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '120301',
                'name' => 'Vehicle',
                'category' => 'Vehicle',
                'parent' => '120300',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120302',
                'name' => 'Accumulated Depreciation - Vehicle',
                'category' => 'Vehicle',
                'parent' => '120300',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120400',
                'name' => 'Equipment',
                'category' => 'Equipment',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '120401',
                'name' => 'Office Equipment',
                'category' => 'Equipment',
                'parent' => '120400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120402',
                'name' => 'Warehouse Equipment',
                'category' => 'Equipment',
                'parent' => '120400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120403',
                'name' => 'Computer & IT Equipment',
                'category' => 'Equipment',
                'parent' => '120400',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '120404',
                'name' => 'Accumulated Depreciation - Equipment',
                'category' => 'Equipment',
                'parent' => '120400',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // CURRENT LIABILITIES
            // ==========================================================

            [
                'code' => '210100',
                'name' => 'Accounts Payable',
                'category' => 'Accounts Payable',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '210101',
                'name' => 'Trade Payable',
                'category' => 'Accounts Payable',
                'parent' => '210100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '210102',
                'name' => 'Other Payable',
                'category' => 'Accounts Payable',
                'parent' => '210100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '210200',
                'name' => 'Tax Payable',
                'category' => 'Tax Payable',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '210201',
                'name' => 'VAT Payable',
                'category' => 'Tax Payable',
                'parent' => '210200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '210202',
                'name' => 'Withholding Tax Payable',
                'category' => 'Tax Payable',
                'parent' => '210200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '210203',
                'name' => 'Income Tax Payable',
                'category' => 'Tax Payable',
                'parent' => '210200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '210204',
                'name' => 'Other Tax Payable',
                'category' => 'Tax Payable',
                'parent' => '210200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // LONG TERM LIABILITIES
            // ==========================================================

            [
                'code' => '220100',
                'name' => 'Bank Loan',
                'category' => 'Bank Loan',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '220101',
                'name' => 'Short Term Bank Loan',
                'category' => 'Bank Loan',
                'parent' => '220100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '220102',
                'name' => 'Long Term Bank Loan',
                'category' => 'Bank Loan',
                'parent' => '220100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '220103',
                'name' => 'Current Portion of Long-term Loan',
                'category' => 'Bank Loan',
                'parent' => '220100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '220104',
                'name' => 'Accurate Loan Interest',
                'category' => 'Bank Loan',
                'parent' => '220100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // EQUITY
            // ==========================================================

            // Owner Capital
            [
                'code' => '310100',
                'name' => 'Owner Capital',
                'category' => 'Owner Capital',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '310101',
                'name' => 'Owner Capital',
                'category' => 'Owner Capital',
                'parent' => '310100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '310102',
                'name' => 'Additional Paid-in Capital',
                'category' => 'Owner Capital',
                'parent' => '310100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '310103',
                'name' => 'Owner Drawings',
                'category' => 'Owner Capital',
                'parent' => '310100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            // Retained Earnings
            [
                'code' => '310200',
                'name' => 'Retained Earnings',
                'category' => 'Retained Earnings',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '310201',
                'name' => 'Retained Earnings',
                'category' => 'Retained Earnings',
                'parent' => '310200',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            // Current Year Earnings
            [
                'code' => '310300',
                'name' => 'Current Year Earnings',
                'category' => 'Current Year Earnings',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '310301',
                'name' => 'Current Year Earnings',
                'category' => 'Current Year Earnings',
                'parent' => '310300',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],
            // Opening Balance Equity
            [
                'code' => '310400',
                'name' => 'Opening Balance Equity',
                'category' => 'Opening Balance Equity',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '310401',
                'name' => 'Opening Balance Equity',
                'category' => 'Opening Balance Equity',
                'parent' => '310400',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            // ==========================================================
            // REVENUE
            // ==========================================================

            [
                'code' => '410100',
                'name' => 'Sales Revenue',
                'category' => 'Sales Revenue',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '410101',
                'name' => 'Merchandise Sales',
                'category' => 'Sales Revenue',
                'parent' => '410100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '410102',
                'name' => 'Service Revenue',
                'category' => 'Sales Revenue',
                'parent' => '410100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '410103',
                'name' => 'Sales Discount',
                'category' => 'Sales Revenue',
                'parent' => '410100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '410104',
                'name' => 'Sales Return',
                'category' => 'Sales Revenue',
                'parent' => '410100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // COST OF GOODS SOLD
            // ==========================================================

            [
                'code' => '510100',
                'name' => 'Cost of Goods Sold',
                'category' => 'Cost of Goods Sold',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '510101',
                'name' => 'Merchandise Cost of Goods Sold',
                'category' => 'Cost of Goods Sold',
                'parent' => '510100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '510102',
                'name' => 'Inventory Adjustment Expense',
                'category' => 'Cost of Goods Sold',
                'parent' => '510100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '510103',
                'name' => 'Other Cost of Goods Sold',
                'category' => 'Cost of Goods Sold',
                'parent' => '510100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // OPERATING EXPENSES
            // ==========================================================

            [
                'code' => '610100',
                'name' => 'Operating Expense',
                'category' => 'Operating Expense',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '610101',
                'name' => 'Salaries & Wages Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610102',
                'name' => 'Employee Benefits Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610103',
                'name' => 'Rent Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610104',
                'name' => 'Utilities Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610105',
                'name' => 'Telephone & Internet Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610106',
                'name' => 'Delivery Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610107',
                'name' => 'Office Supplies Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610108',
                'name' => 'Printing & Stationery Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610109',
                'name' => 'Repairs & Maintenance Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610110',
                'name' => 'Advertising & Marketing Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610111',
                'name' => 'Depreciation Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610112',
                'name' => 'Bank Charges Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610113',
                'name' => 'Other Operating Expense',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '610114',
                'name' => 'Service Charge - Bazzar',
                'category' => 'Operating Expense',
                'parent' => '610100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],


            // ==========================================================
            // OTHER INCOME
            // ==========================================================

            [
                'code' => '710100',
                'name' => 'Other Income',
                'category' => 'Other Income',
                'parent' => null,
                'normal_balance' => 'Credit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '710101',
                'name' => 'Interest Income',
                'category' => 'Other Income',
                'parent' => '710100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '710102',
                'name' => 'Gain on Asset Disposal',
                'category' => 'Other Income',
                'parent' => '710100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '710103',
                'name' => 'Other Income',
                'category' => 'Other Income',
                'parent' => '710100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '710104',
                'name' => 'Inventory Adjustment Gain',
                'category' => 'Other Income',
                'parent' => '710100',
                'normal_balance' => 'Credit',
                'is_header' => false,
                'is_posting' => true,
            ],
            // ==========================================================
            // OTHER EXPENSE
            // ==========================================================

            [
                'code' => '810100',
                'name' => 'Other Expense',
                'category' => 'Other Expense',
                'parent' => null,
                'normal_balance' => 'Debit',
                'is_header' => true,
                'is_posting' => false,
            ],

            [
                'code' => '810101',
                'name' => 'Interest Expense',
                'category' => 'Other Expense',
                'parent' => '810100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '810102',
                'name' => 'Loss on Asset Disposal',
                'category' => 'Other Expense',
                'parent' => '810100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

            [
                'code' => '810103',
                'name' => 'Other Expense',
                'category' => 'Other Expense',
                'parent' => '810100',
                'normal_balance' => 'Debit',
                'is_header' => false,
                'is_posting' => true,
            ],

        ];

        foreach ($accounts as $account) {

            $category = AccountCategory::where(
                'name',
                $account['category']
            )->firstOrFail();

            $parent = null;
            $level = 1;

            if ($account['parent']) {

                $parent = ChartOfAccount::where(
                    'code',
                    $account['parent']
                )
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->first();

                $level = $parent
                    ? $parent->level + 1
                    : 1;
            }

            ChartOfAccount::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $account['code'],
                ],
                [
                    'parent_id' => $parent?->id,
                    'account_category_id' => $category->id,
                    'name' => $account['name'],
                    'normal_balance' => $account['normal_balance'],
                    'level' => $level,
                    'is_header' => $account['is_header'],
                    'is_posting' => $account['is_posting'],
                    'opening_balance' => 0,
                    'status' => true,
                    'description' => null,
                ]
            );
        }
    }
}