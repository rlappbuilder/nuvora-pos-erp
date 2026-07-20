
Defaultdata 


┌─────────────────────────────────────────────┐
│ Header                                      │
├─────────────────────────────────────────────┤
│ Summary Cards                               │
├─────────────────────────────────────────────┤
│ Toolbar                                     │
├─────────────────────────────────────────────┤
│ DataTable                                   │
├─────────────────────────────────────────────┤
│ Pagination                                  │
└─────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────┐
│ 🔍 Search...                         [+ New Cash Bank]   │
│                                                          │
│ Type ▼   Status ▼      Reset Filter                      │
└──────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────┐
│☐│ Code │ Name │ Type │ Balance │ Status │ Actions        │
├──────────────────────────────────────────────────────────┤
│☐│CB001 │Kas...│Cash  │Rp....   │🟢      │👁️ ✏️ 🗑️          │
│☐│CB002 │Bank..│Bank  │Rp....   │🟢      │👁️ ✏️ 🗑️          │
└──────────────────────────────────────────────────────────┘

Components/
└── Table/
    ├── DataTable.vue
    ├── DataTableHead.vue
    ├── DataTableHeaderCell.vue
    ├── DataTableBody.vue
    ├── DataTableRow.vue
    ├── DataTableCell.vue
    ├── DataTableToolbar.vue
    ├── TableEmpty.vue
    └── TableLoading.vue

    Components/

Card/
├── BaseCard.vue
├── StatsCard.vue ⭐
├── InfoCard.vue
└── SectionCard.vue

Layout/
├── PageHeader.vue
├── ActionBar.vue

    🚀 Tetapi saya ingin mengubah sedikit strategi.
Saya tidak ingin langsung menulis 400 baris Index.vue.
Kita pecah menjadi beberapa sprint agar tetap rapi:
Sprint 1
Import
Props
State
Watch
Computed
Methods
Sprint 2
Header
Summary Cards
Sprint 3
Toolbar
Sprint 4
DataTable
Sprint 5
Pagination
Sprint 6
Delete Modal

Tapi... sekarang saya masuk mode UI/UX Architect 😎
Kalau target kita adalah kelas Odoo / SAP / Oracle NetSuite, ada beberapa hal yang menurut saya perlu dipoles.
1. Stats Card terlalu kosong
Sekarang tampilannya seperti:
Total Account

7
Padahal ruangnya masih banyak.
Saya ingin nanti seperti ini.
Total Account

7 Accounts

+2 this month
atau
Current Balance

Rp428.976.544

Updated Today
Jadi card terasa "hidup".