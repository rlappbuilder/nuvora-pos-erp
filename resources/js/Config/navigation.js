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
             modules.purchaseRequests,
             modules.purchaseOrders,
             modules.GoodsReceipts,
             modules.purchaseInvoice,
             modules.purchasePayment,
             modules.purchaseReturns,
             modules.apAging,
             modules.purchaseReport,
             modules.supplierStatement,
           
        ],
    },
     {
            title: 'PointOfSales',
            children: [
                 modules.dashboards,
                 modules.PointOfSales,
                
                // modules.deliveryOrders,
                // modules.salesInvoices,
                // modules.salesReturns,
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
        modules.Resellers,
        modules.ConsignmentOuts,
        modules.ConsignmentStocks,
        modules.ConsignmentSettlements,
        modules.ConsignmentReceivables,
        modules.ConsignmentReturns,
        modules.ConsignmentReports,
        modules.ResellerStatements,
        modules.ResellerMutations,
        ],
    },

    {
        title: 'Accounting',
        children: [
            modules.cashBanks,
            modules.chartOfAccounts,
            modules.accountingPeriod,
            modules.fiscalYears,
            modules.accountingJournals,
            modules.journalsEntries,
             modules.generalLeders,
            
            modules.trialBalances,
            modules.balanceSheets,
            modules.profitLosses,
           
        ].filter(Boolean),
    },
{
        title: 'User',
        children: [
            modules.User,
            //modules.fifoengin,
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