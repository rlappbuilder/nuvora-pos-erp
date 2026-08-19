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
            modules.size,
            modules.companies,
            modules.warehouse,
            modules.branches,
            modules.products,
            modules.productsattributes,
            modules.productsAttributeValues,
            modules.productsVariants,
            modules.productsVariantUnits,
            modules.productsPrice,
            modules.customers,
            modules.suppliers,
            modules.tax,
            modules.currency,
        ].filter(Boolean),
        
    },

    {
        title: 'Inventory',
        children: [
            modules.openingStock,
            modules.stockAdjustments,
             modules.stockTransfers,
             modules.stockIssues,
             modules.stockOpname,
             modules.stockBalance,
              
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
            modules.fifoengin,
            // modules.generalLedgers,
            // modules.trialBalances,
            // modules.profitLosses,
            // modules.balanceSheets,
        ].filter(Boolean),
    },
]

export default navigation