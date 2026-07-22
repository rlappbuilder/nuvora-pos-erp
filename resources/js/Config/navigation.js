import modules from './modules'

const navigation = [
    modules.dashboard,

    {
        title: 'Master Data',
        children: [
            modules.categories,
            modules.units,
            modules.brands,
            modules.colors,
            modules.companies,
            modules.branches,
            modules.warehouses,
            modules.products,
            modules.customers,
            modules.suppliers,
        ].filter(Boolean),
    },

    {
        title: 'Inventory',
        children: [
            modules.openingStock,
            modules.stockAdjustments,
             modules.stockTransfers,
             modules.stockBalance,
              modules.stockIssues,
             modules.stockCards,
            
            // modules.stockOpnames,
        ],
    },

    {
        title: 'Purchasing',
        children: [
            // modules.purchaseRequests,
             modules.purchaseOrders,
             modules.GoodReceipts,
             modules.purchaseInvoice,
             modules.purchasePayment,
             modules.purchaseReturns,
        ],
    },

    {
        title: 'Sales',
        children: [
            // modules.quotations,
            // modules.salesOrders,
            // modules.deliveryOrders,
            // modules.salesInvoices,
            // modules.salesReturns,
        ],
    },

  {
        title: 'Resellers',
        children: [
            // modules.quotations,
            // modules.salesOrders,
            // modules.deliveryOrders,
            // modules.salesInvoices,
            // modules.salesReturns,
        ],
    },

    {
        title: 'Accounting',
        children: [
            modules.cashBanks,
             modules.chartOfAccounts,
            
            // modules.journals,
            // modules.generalLedgers,
            // modules.trialBalances,
            // modules.profitLosses,
            // modules.balanceSheets,
        ].filter(Boolean),
    },

     {
        title: 'Settings',
        children: [
            modules.Settings,
            
            // modules.journals,
            // modules.generalLedgers,
            // modules.trialBalances,
            // modules.profitLosses,
            // modules.balanceSheets,
        ].filter(Boolean),
    },
]

export default navigation