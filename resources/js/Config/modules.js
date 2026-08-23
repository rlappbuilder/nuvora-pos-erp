// resources/js/Config/modules.js

import { currency } from '@/Utils'
import {
    HomeIcon,
    BuildingOfficeIcon,
    BuildingStorefrontIcon,
    CubeIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline'
import { Warehouse } from 'lucide-vue-next'

const modules = {
    dashboard: {
        key: 'dashboard',
        title: 'Dashboard',
        icon: HomeIcon,
        route: 'dashboard',
        permission: 'dashboard.view',
    },


      categories: {
        key: 'categories',
        title: 'Categories',
        icon: BuildingOfficeIcon,
        route: 'categories.index',
        permission: 'categories.view',
    },

    units: {
        key: 'units',
        title: 'Units',
        icon: BuildingOfficeIcon,
        route: 'units.index',
        permission: 'units.view',
    },

    brands: {
        key: 'brands',
        title: 'Brands',
        icon: BuildingOfficeIcon,
        route: 'brands.index',
        permission: 'brands.view',
    },

    colors: {
        key: 'colors',
        title: 'Colors',
        icon: BuildingOfficeIcon,
        route: 'colors.index',
        permission: 'colors.view',
    },
     size: {
        key: 'size',
        title: 'Size',
        icon: BuildingOfficeIcon,
        route: 'sizes.index',
        permission: 'sizes.view',
    },
    

     companies: {
        key: 'companies',
        title: 'Company',
        icon: BuildingOfficeIcon,
        route: 'companies.index',
        permission: 'companies.view',
    },

  
     warehouse: {
        key: 'warehouse',
        title: 'Warehouse',
        icon: UsersIcon,
        route: 'warehouses.index',
        permission: 'warehouse.view',
    },
    branches: {
        key: 'branches',
        title: 'Branch',
        icon: BuildingStorefrontIcon,
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
        icon: CubeIcon,
        route: 'product-attributes.index',
        permission: 'product-attributes.view',
    },
        productsAttributeValues: {
       key: 'productsAttributeValues',
       title: 'Attribute Values',
          icon: CubeIcon,
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
          icon: CubeIcon,
          route: 'product-variant-units.index',
        permission: 'product-variant-units.view',
     },
     productsPrice: {
       key: 'productsPrice',
       title: 'Product Price',
          icon: CubeIcon,
          route: 'product-variant-prices.index',
        permission: 'product-variant-prices.view',
     },
     tax: {
        key: 'tax',
        title: 'Tax',
        icon: CubeIcon,
        route: 'taxes.index',
        permission: 'taxes.index',
    },
     currency: {
        key: 'currency',
        title: 'Currency',
        icon: CubeIcon,
        route: 'currencies.index',
        permission: 'currencies.index',
    },

     suppliers: {
        key: 'suppliers',
        title: 'Suppliers',
        icon: UsersIcon,
        route: 'suppliers.index',
        permission: 'suppliers.view',
    },
    customers: {
        key: 'customers',
        title: 'Customer',
        icon: UsersIcon,
        route: 'customers.index',
        permission: 'customers.view',
    },
        // inventory //
                 openingStock: {
                key: 'openingStock',
                title: 'Opening Stock',
                icon: UsersIcon,
                route: 'opening-stocks.index',
                permission: 'opening-stocks.view',
                  },

               stockAdjustments: {
                key: 'stockAdjustments',
                title: 'Stock Adjustment',
                icon: UsersIcon,
                route: 'inventory-adjustments.index',
                permission: 'inventory-adjustments.view',
                 },

                    stockBalance: {
                key: 'stockBalance',
                title: 'Stock Balance',
                icon: UsersIcon,
                route: 'stock-balance.index',
                permission: 'stock-balance.view',
                 },
                       stockOpname: {
                key: 'stockOpname',
                title: 'Stock Opname',
                icon: UsersIcon,
                route: 'stock-opnames.index',
                permission: 'stock-opnames.view',
                 },

                   stockTransfers: {
                key: 'stockTransfers',
                title: 'Stock Transfers',
                icon: UsersIcon,
                route: 'stock-transfers.index',
                permission: 'stock-transfers.view',
                   },
                   

                    stockIssues: {
                key: 'stockIssues',
                title: 'Stock Issues',
                icon: UsersIcon,
                route: 'stock-issues.index',
                permission: 'stock-issues.view',
                },

                 stockCards: {
                key: 'stockCards',
                title: 'Stock Cards',
                icon: UsersIcon,
                route: 'stock-card.index',
                permission: 'stock-card.view',
                 },

        // end inventory //
        //Accounting //
            cashBanks: {
                key: 'cashBanks',
                title: 'Cash Bank',
                icon: UsersIcon,
                route: 'cash-banks.index',
                permission: 'cash-banks.view',
            },
            
            chartOfAccounts: {
                key: 'chartOfAccounts',
                title: 'Charts Of Account',
                icon: UsersIcon,
                route: 'chart-of-accounts.index',
                permission: 'chart-of-accounts.view',
            },

        // End accounting //

           //purchasing //
           
            purchaseRequests: {
                key: 'purchaseRequests',
                title: 'Purchase Request',
                icon: UsersIcon,
                route: 'purchase-requests.index',
                permission: 'purchase-requests.view',
            },
            purchaseOrders: {
                key: 'purchaseOrders',
                title: 'Purchase order',
                icon: UsersIcon,
                route: 'purchase-orders.index',
                permission: 'purchase-orders.view',
            },
            
            GoodReceipts: {
                key: 'GoodReceipts',
                title: 'Good Receipts',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },

              purchaseInvoice: {
                key: 'purchaseInvoice',
                title: 'Purchase Invoice',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },

              purchasePayment: {
                key: 'purchasePayment',
                title: 'Purchase Payment',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },

            purchaseReturns: {
                key: 'purchaseReturns',
                title: 'Purchase Returns',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },
        // End purchasing //
        // settings
            Settings: {
                key: 'Settings',
                title: 'Documents Numbering',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },
             fifoengin: {
                key: 'fifoengin',
                title: 'Fifo Engine',
                icon: UsersIcon,
                route: 'goods-receipts.index',
                permission: 'goods-receipts.view',
            },
        // End Settings //
}

export default modules