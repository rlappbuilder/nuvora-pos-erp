import {
    HomeIcon,

    // Master Data
    UsersIcon,
    TagIcon,
    ScaleIcon,
    SparklesIcon,
    PaintBrushIcon,
    WrenchScrewdriverIcon,
    BuildingOfficeIcon,
    BuildingOffice2Icon,
    BuildingStorefrontIcon,
    UserIcon,
    UserGroupIcon,
    CubeIcon,
    Squares2X2Icon,
    RectangleStackIcon,
    QrCodeIcon,
    CurrencyDollarIcon,
    ReceiptPercentIcon,

    // POS
    ComputerDesktopIcon,
    ShoppingCartIcon,
    BanknotesIcon,

    // Inventory
    ArchiveBoxIcon,
    ArrowsRightLeftIcon,
    AdjustmentsHorizontalIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,

    // Accounting
    WalletIcon,
    BookOpenIcon,
    CalendarDaysIcon,
    CalculatorIcon,
    ChartBarIcon,
    CreditCardIcon,

    // Purchasing
    ShoppingBagIcon,
    TruckIcon,

    // User & Settings
    Cog6ToothIcon,
    HashtagIcon,
} from '@heroicons/vue/24/outline'

const modules = {

    // =====================================================
    // DASHBOARD
    // =====================================================

    dashboard: {
        key: 'dashboard',
        title: 'Dashboard',
        icon: HomeIcon,
        route: 'dashboard',
        permission: 'dashboard.view',
    },


    // =====================================================
    // MASTER DATA
    // =====================================================

    categories: {
        key: 'categories',
        title: 'Categories',
        icon: TagIcon,
        route: 'categories.index',
        permission: 'categories.view',
    },

    units: {
        key: 'units',
        title: 'Units',
        icon: ScaleIcon,
        route: 'units.index',
        permission: 'units.view',
    },

    brands: {
        key: 'brands',
        title: 'Brands',
        icon: SparklesIcon,
        route: 'brands.index',
        permission: 'brands.view',
    },

    colors: {
        key: 'colors',
        title: 'Colors',
        icon: PaintBrushIcon,
        route: 'colors.index',
        permission: 'colors.view',
    },

    size: {
        key: 'size',
        title: 'Size',
        icon: WrenchScrewdriverIcon,
        route: 'sizes.index',
        permission: 'sizes.view',
    },

    companies: {
        key: 'companies',
        title: 'Company',
        icon: BuildingOffice2Icon,
        route: 'companies.index',
        permission: 'companies.view',
    },

    employee: {
        key: 'employee',
        title: 'Employee',
        icon: UserIcon,
        route: 'employees.index',
        permission: 'employees.view',
    },

    warehouse: {
        key: 'warehouse',
        title: 'Warehouse',
        icon: BuildingStorefrontIcon,
        route: 'warehouses.index',
        permission: 'warehouse.view',
    },

    branches: {
        key: 'branches',
        title: 'Branch',
        icon: BuildingOfficeIcon,
        route: 'branches.index',
        permission: 'branches.view',
    },

    products: {
        key: 'products',
        title: 'Product',
        icon: CubeIcon,
        route: 'products.index',
        permission: 'products.view',
    },

    productsattributes: {
        key: 'productsattributes',
        title: 'Product Attribute',
        icon: Squares2X2Icon,
        route: 'product-attributes.index',
        permission: 'product-attributes.view',
    },

    productsAttributeValues: {
        key: 'productsAttributeValues',
        title: 'Attribute Values',
        icon: RectangleStackIcon,
        route: 'product-attribute-values.index',
        permission: 'product-attribute-values.view',
    },

    productsVariants: {
        key: 'productsVariants',
        title: 'Product Variant',
        icon: CubeIcon,
        route: 'product-variants.index',
        permission: 'product-variants.view',
    },

    productsVariantUnits: {
        key: 'productsVariantUnits',
        title: 'Variant Units',
        icon: ScaleIcon,
        route: 'product-variant-units.index',
        permission: 'product-variant-units.view',
    },

    productsPrice: {
        key: 'productsPrice',
        title: 'Product Price',
        icon: CurrencyDollarIcon,
        route: 'product-variant-prices.index',
        permission: 'product-variant-prices.view',
    },

    barcodes: {
        key: 'barcodes',
        title: 'Barcodes',
        icon: QrCodeIcon,
        route: 'barcodes.index',
        permission: 'barcodes.view',
    },

    tax: {
        key: 'tax',
        title: 'Tax',
        icon: ReceiptPercentIcon,
        route: 'taxes.index',
        permission: 'taxes.index',
    },

    currency: {
        key: 'currency',
        title: 'Currency',
        icon: CurrencyDollarIcon,
        route: 'currencies.index',
        permission: 'currencies.index',
    },

    suppliers: {
        key: 'suppliers',
        title: 'Suppliers',
        icon: TruckIcon,
        route: 'suppliers.index',
        permission: 'suppliers.view',
    },

    customers: {
        key: 'customers',
        title: 'Customer',
        icon: UserGroupIcon,
        route: 'customers.index',
        permission: 'customers.view',
    },


    // =====================================================
    // POINT OF SALES
    // =====================================================

    dashboards: {
        key: 'dashboards',
        title: 'Dashboard',
        icon: ComputerDesktopIcon,
        route: 'pos.cashier.index',
        permission: 'pos.cashier.index.view',
    },

    PointOfSales: {
        key: 'PointOfSales',
        title: 'Point Of Sales',
        icon: ShoppingCartIcon,
        route: 'pos.transactions.index',
        permission: 'pos.transactions.view',
    },

    backdatesales: {
        key: 'backdatesales',
        title: 'Cashier',
        icon: BanknotesIcon,
        route: 'pos.backdate-sales.index',
        permission: 'backdate-sales.view',
    },


    // =====================================================
    // INVENTORY
    // =====================================================

    openingStock: {
        key: 'openingStock',
        title: 'Opening Stock',
        icon: ArchiveBoxIcon,
        route: 'opening-stocks.index',
        permission: 'opening-stocks.view',
    },

    stockAdjustments: {
        key: 'stockAdjustments',
        title: 'Stock Adjustment',
        icon: AdjustmentsHorizontalIcon,
        route: 'inventory-adjustments.index',
        permission: 'inventory-adjustments.view',
    },

    stockBalance: {
        key: 'stockBalance',
        title: 'Stock Balance',
        icon: CubeIcon,
        route: 'stock-balance.index',
        permission: 'stock-balance.view',
    },

    stockOpname: {
        key: 'stockOpname',
        title: 'Stock Opname',
        icon: ClipboardDocumentCheckIcon,
        route: 'stock-opnames.index',
        permission: 'stock-opnames.view',
    },

    stockTransfers: {
        key: 'stockTransfers',
        title: 'Stock Transfers',
        icon: ArrowsRightLeftIcon,
        route: 'stock-transfers.index',
        permission: 'stock-transfers.view',
    },

    stockIssues: {
        key: 'stockIssues',
        title: 'Stock Issues',
        icon: ClipboardDocumentListIcon,
        route: 'stock-issues.index',
        permission: 'stock-issues.view',
    },

    stockCards: {
        key: 'stockCards',
        title: 'Stock Cards',
        icon: DocumentTextIcon,
        route: 'stock-card.index',
        permission: 'stock-card.view',
    },
    
         // =====================================================
        // PRODUCTION
        // =====================================================

        productionDashboard: {
            key: 'productionDashboard',
            title: 'Dashboard',
            icon: BuildingOffice2Icon,
            route: 'production-operations.index',
            permission: 'production.dashboard.view',
        },

        productionOperations: {
            key: 'productionOperations',
            title: 'Production Operations',
            icon: WrenchScrewdriverIcon,
            route: 'production-operations.index',
            permission: 'production-operations.view',
        },

    // =====================================================
    // ACCOUNTING
    // =====================================================

    cashBanks: {
        key: 'cashBanks',
        title: 'Cash Bank',
        icon: WalletIcon,
        route: 'cash-banks.index',
        permission: 'cash-banks.view',
    },

    chartOfAccounts: {
        key: 'chartOfAccounts',
        title: 'Charts Of Account',
        icon: BookOpenIcon,
        route: 'chart-of-accounts.index',
        permission: 'chart-of-accounts.view',
    },

    accountingPeriod: {
        key: 'accountingPeriod',
        title: 'Accounting Period',
        icon: CalendarDaysIcon,
        route: 'accounting-periods.index',
        permission: 'accounting-periods.view',
    },

    accountingJournals: {
        key: 'accountingJournals',
        title: 'Accounting Journals',
        icon: BookOpenIcon,
        route: 'accounting-journals.index',
        permission: 'accounting-journals.view',
    },

    fiscalYears: {
        key: 'fiscalYears',
        title: 'Fiscal Years',
        icon: CalendarDaysIcon,
        route: 'fiscal-years.index',
        permission: 'fiscal-years.view',
    },

    journalsEntries: {
        key: 'journalsEntries',
        title: 'Journal Entry',
        icon: DocumentTextIcon,
        route: 'journal-entries.index',
        permission: 'journal-entries.view',
    },

    generalLeders: {
        key: 'generalLeders',
        title: 'General Ledger',
        icon: BookOpenIcon,
        route: 'general-ledgers.index',
        permission: 'general-ledgers.view',
    },

    trialBalances: {
        key: 'trialBalances',
        title: 'Trial Balance',
        icon: CalculatorIcon,
        route: 'trial-balance.index',
        permission: 'trial-balance.view',
    },

    balanceSheets: {
        key: 'balanceSheets',
        title: 'Balance Sheet',
        icon: ScaleIcon,
        route: 'balance-sheet.index',
        permission: 'balance-sheet.view',
    },

    profitLosses: {
        key: 'profitLosses',
        title: 'Income Statement',
        icon: ChartBarIcon,
        route: 'income-statement.index',
        permission: 'income-statement.view',
    },

    fundTransfers: {
        key: 'fundTransfers',
        title: 'Fund Transfer',
        icon: ArrowsRightLeftIcon,
        route: 'fund-transfers.index',
        permission: 'fund-transfers.view',
    },


    // =====================================================
    // PURCHASING
    // =====================================================

    purchaseRequests: {
        key: 'purchaseRequests',
        title: 'Purchase Request',
        icon: ClipboardDocumentListIcon,
        route: 'purchase-requests.index',
        permission: 'purchase-requests.view',
    },

    purchaseOrders: {
        key: 'purchaseOrders',
        title: 'Purchase Order',
        icon: ShoppingBagIcon,
        route: 'purchase-orders.index',
        permission: 'purchase-orders.view',
    },

    GoodsReceipts: {
        key: 'GoodsReceipts',
        title: 'Good Receipts',
        icon: TruckIcon,
        route: 'goods-receipts.index',
        permission: 'goods-receipts.view',
    },

    purchaseReturns: {
        key: 'purchaseReturns',
        title: 'Purchase Return',
        icon: ArrowsRightLeftIcon,
        route: 'purchase-returns.index',
        permission: 'purchase-returns.view',
    },

    purchaseInvoice: {
        key: 'purchaseInvoice',
        title: 'Purchase Invoice',
        icon: DocumentTextIcon,
        route: 'purchase-invoices.index',
        permission: 'purchase-invoices.view',
    },

    purchasePayment: {
        key: 'purchasePayment',
        title: 'Purchase Payment',
        icon: CreditCardIcon,
        route: 'purchase-payments.index',
        permission: 'purchase-payments.view',
    },

    purchaseReport: {
        key: 'purchaseReport',
        title: 'Purchase Report',
        icon: ChartBarIcon,
        route: 'reports.purchase-report',
        permission: 'reports.purchase-report.view',
    },

    supplierStatement: {
        key: 'supplierStatement',
        title: 'Supplier Statement',
        icon: DocumentTextIcon,
        route: 'reports.supplier-statement',
        permission: 'reports.supplier-statement.view',
    },

    apAging: {
        key: 'apAging',
        title: 'AP Aging',
        icon: CalendarDaysIcon,
        route: 'reports.ap-aging',
        permission: 'reports.ap-aging.view',
    },


    // =====================================================
    // RESELLERS & CONSIGNMENT
    // =====================================================

    Resellers: {
        key: 'Resellers',
        title: 'Resellers',
        icon: UsersIcon,
        route: 'resellers.index',
        permission: 'resellers.index.view',
    },

    ConsignmentOuts: {
        key: 'ConsignmentOuts',
        title: 'Consignment Out',
        icon: TruckIcon,
        route: 'consignment-outs.index',
        permission: 'consignment-outs.view',
    },

    ConsignmentStocks: {
        key: 'ConsignmentStocks',
        title: 'Consignment Stock',
        icon: CubeIcon,
        route: 'consignment-stock.index',
        permission: ' consignment-stock.view',
    },

    ConsignmentSettlements: {
        key: 'ConsignmentSettlements',
        title: 'Consignment Settlement',
        icon: CalculatorIcon,
        route: 'consignment-settlements.index',
        permission: 'consignment-settlements.view',
    },

    ConsignmentReceivables: {
        key: 'ConsignmentReceivables',
        title: 'Consignment Receivable',
        icon: CreditCardIcon,
        route: 'consignment-receivables.index',
        permission: 'consignment-receivables.view',
    },

    ConsignmentReturns: {
        key: 'ConsignmentReturns',
        title: 'Consignment Returns',
        icon: ArrowsRightLeftIcon,
        route: 'consignment-returns.index',
        permission: 'consignment-returns.view',
    },

    ResellerStatements: {
        key: 'ResellerStatements',
        title: 'Reseller Statement',
        icon: DocumentTextIcon,
        route: 'reseller-statements.index',
        permission: 'reseller-statements.view',
    },

    ResellerMutations: {
        key: 'ResellerMutations',
        title: 'Reseller Mutation',
        icon: ArrowsRightLeftIcon,
        route: 'reseller-mutations.index',
        permission: 'reseller-mutations.view',
    },

    ConsignmentReports: {
        key: 'ConsignmentReports',
        title: 'Consignment Reports',
        icon: ChartBarIcon,
        route: 'consignment-reports.index',
        permission: 'consignment-reports.view',
    },

    // =====================================================
    // Report Catalog
    // =====================================================
         reportCatalog: {
            key: 'reportCatalog',
            title: 'Report Catalog',
            icon: ChartBarIcon,
            route: 'reports.index',
            permission: 'reports.view',
        },

        reportHistory: {
            key: 'reportHistory',
            title: 'Report History',
            icon: ChartBarIcon,
            route: 'consignment-reports.index',
            permission: 'consignment-reports.view',
        },


    // =====================================================
    // USER MANAGEMENT
    // =====================================================

    User: {
        key: 'User',
        title: 'User Config',
        icon: UserGroupIcon,
        route: 'users.index',
        permission: 'users.view',
    },


    // =====================================================
    // SETTINGS
    // =====================================================

    Settings: {
        key: 'Settings',
        title: 'Documents Numbering',
        icon: HashtagIcon,
        route: 'goods-receipts.index',
        permission: 'goods-receipts.view',
    },

    fifoengin: {
        key: 'fifoengin',
        title: 'Fifo Engine',
        icon: Cog6ToothIcon,
        route: 'goods-receipts.index',
        permission: 'goods-receipts.view',
    },

}

export default modules