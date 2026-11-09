import modules from './modules'

import {
    Squares2X2Icon,
    ArchiveBoxIcon,
    ShoppingCartIcon,
    ComputerDesktopIcon,
    ChartBarIcon,
    UsersIcon,
    DocumentChartBarIcon,
    CalculatorIcon,
    UserGroupIcon,
    Cog6ToothIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

const navigation = [

    // =====================================================
    // DASHBOARD
    // =====================================================

    modules.dashboard,


    // =====================================================
    // MASTER DATA
    // =====================================================

    {
        title: 'Master Data',
        icon: Squares2X2Icon,
        children: [
            modules.categories,
            modules.units,
            modules.brands,
            modules.colors,
            modules.size,
            modules.companies,
            modules.employee,
            modules.warehouse,
            modules.branches,
            modules.products,
            modules.productsattributes,
            modules.productsAttributeValues,
            modules.productsVariants,
            modules.productsVariantUnits,
            modules.productsPrice,
            modules.barcodes,
            modules.customers,
            modules.suppliers,
            modules.tax,
            modules.currency,
        ].filter(Boolean),
    },
    // =====================================================
    // PRODUCTION
    // =====================================================

    {
        title: 'Production',
        icon: WrenchScrewdriverIcon,
        children: [
            modules.productionOperations,
        ].filter(Boolean),
    },

    // =====================================================
    // INVENTORY
    // =====================================================

    {
        title: 'Inventory',
        icon: ArchiveBoxIcon,
        children: [
            modules.openingStock,
            modules.stockAdjustments,
            modules.stockTransfers,
            modules.stockIssues,
            modules.stockOpname,
            modules.stockBalance,
            modules.stockCards,
        ].filter(Boolean),
    },


    // =====================================================
    // PURCHASING
    // =====================================================

    {
        title: 'Purchasing',
        icon: ShoppingCartIcon,
        children: [
            modules.purchaseRequests,
            modules.purchaseOrders,
            modules.GoodsReceipts,
            modules.purchaseInvoice,
            modules.purchasePayment,
            modules.purchaseReturns,
            modules.apAging,
            modules.purchaseReport,
            modules.supplierStatement,
        ].filter(Boolean),
    },


    // =====================================================
    // POINT OF SALES
    // =====================================================

    {
        title: 'PointOfSales',
        icon: ComputerDesktopIcon,
        children: [
            modules.dashboards,
            modules.PointOfSales,
            modules.backdatesales,
        ].filter(Boolean),
    },


    // =====================================================
    // SALES
    // =====================================================

    {
        title: 'Sales',
        icon: ChartBarIcon,
        children: [
            // modules.quotations,
            // modules.salesOrders,
            // modules.deliveryOrders,
            // modules.salesInvoices,
            // modules.salesReturns,
        ],
    },


    // =====================================================
    // RESELLERS
    // =====================================================

    {
        title: 'Resellers',
        icon: UsersIcon,
        children: [
            modules.Resellers,
            modules.ConsignmentOuts,
            modules.ConsignmentStocks,
            modules.ConsignmentSettlements,
            modules.ConsignmentReceivables,
            modules.ConsignmentReturns,
            modules.ConsignmentReports,
            modules.ResellerStatements,
            modules.ResellerMutations,
        ].filter(Boolean),
    },


    // =====================================================
    // REPORTS
    // =====================================================

    {
        title: 'Reports',
        icon: DocumentChartBarIcon,
        children: [
            modules.reportCatalog,
            modules.reportHistory,
        ],
    },


    // =====================================================
    // ACCOUNTING
    // =====================================================

    {
        title: 'Accounting',
        icon: CalculatorIcon,
        children: [
            modules.cashBanks,
            modules.chartOfAccounts,
            modules.accountingPeriod,
            modules.fiscalYears,
            modules.accountingJournals,
            modules.journalsEntries,
            modules.generalLeders,
            modules.fundTransfers,
            modules.trialBalances,
            modules.balanceSheets,
            modules.profitLosses,
        ].filter(Boolean),
    },


    // =====================================================
    // USER MANAGEMENT
    // =====================================================

    {
        title: 'User',
        icon: UserGroupIcon,
        children: [
            modules.User,
        ].filter(Boolean),
    },


    // =====================================================
    // SETTINGS
    // =====================================================

    {
        title: 'Settings',
        icon: Cog6ToothIcon,
        children: [
            modules.Settings,
            modules.fifoengin,
        ].filter(Boolean),
    },

]

export default navigation