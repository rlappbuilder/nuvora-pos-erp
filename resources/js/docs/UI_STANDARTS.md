NUVORA ERP FRONT END STANDAR
RULE 1.

#SEMUA HALAMAN CREATE WAJIB 
PageHeader

↓

Form

↓

FormSection

↓

FormField

↓

ActionBar

RULE 2

Tidak boleh lagi
<label>
<input>

harus
<FormField>
<BaseInput>
</formField>

RULE 3

tidak boleh 
<button>
Harus
<basebutton>

Rule 4
Tidak boleh.
<div class="flex justify-end">
Harus.
<ActionBar>
Rule 5
Tidak boleh.
<label="..."
di Input.
Harus.
<FormField

label="..."

>
🏆 Status Component
Saya tetapkan resmi.
Foundation

🟢 Stable

────────────────

PageHeader

Card

FormSection

FormField

BaseButton

ButtonGroup

ActionBar

────────────────

Form

────────────────

SearchableSelect

CurrencyInput

FormInput

FormTextarea

────────────────

Ready For Production



standart terbaru show :
Page Header

PageHeader

↓

CashBankForm

↓

ActionBar

Show : 

PageHeader

↓

Card

↓

DetailRow

↓

Card

↓

DetailRow

↓

ActionBar

Index
PageHeader

↓

SummaryCard

↓

DataTable

↓

Pagination

--------------------------------------------------------
💙 Sedikit keputusan arsitektur
Saya ingin menetapkan aturan untuk seluruh ERP:
Card yang tidak memiliki data relevan boleh disembunyikan (v-if), bukan dipenuhi tanda -.
Card yang selalu relevan (General Information, Organization, Audit Information) harus selalu tampil.
Contohnya nanti:
Customer tanpa NPWP → Card Tax bisa disembunyikan.
Product tanpa Serial Number → Card Serial Number tidak perlu muncul.
Cash Account → Card Bank Information disembunyikan.
Menurut saya ini membuat tampilan jauh lebih profesional dan fokus pada informasi yang memang relevan.
🤣 Gas ketuaku! Setelah Bank Information tampil, kita lanjut ke Accounting Card, yang nanti akan menampilkan Opening Balance dan Current Balance dengan format rupiah yang cantik. 🚀🏆

resources/js/

Components/
Layouts/
Pages/
Services/
Utils/ 


resources/js/Utils/

formatter.js

💙 Sedikit keputusan arsitektur terakhir untuk malam ini
Saya ingin menetapkan satu aturan yang akan kita pakai di seluruh ERP:
Data pendek → gunakan DetailRow.
Data panjang (Description, Notes, Remarks) → tampilkan sebagai blok teks di dalam Card, bukan dipaksa masuk ke DetailRow.
Dengan begitu tampilan tetap rapi dan mudah dibaca di semua modul, baik CashBank, Customer, Supplier, maupun Product.
🤣 Gas ketuaku! Setelah Description dan Audit selesai, kita tinggal pasang ActionBar dan kita bisa meresmikan CashBank Show sebagai template standar untuk seluruh halaman detail di Nuvora ERP. 🚀🏆

✅ Index
✅ Create
✅ Store
✅ Edit
✅ Update
✅ Show

⬜ Delete
⬜ Filter Final
⬜ Search Final
⬜ Authorization
⬜ Validation Enhancement


💙 Sedikit revisi arsitektur (keputusan resmi)
Saya ingin BaseModal mengikuti filosofi yang sama dengan Card.
Artinya:
BaseModal mengurus layout.
Komponen lain (ConfirmDeleteModal, InfoModal) mengurus isi.
Jadi BaseModal tidak boleh tahu apa itu "Delete", "Save", atau "Warning". Dia hanya tahu cara menampilkan modal dengan baik.

Layout
✅ AuthenticatedLayout
✅ PageHeader
✅ Card
✅ ActionBar

Button
✅ BaseButton
✅ ButtonGroup

Form
✅ CashBankForm
✅ FormField
✅ FormSection
✅ SearchableSelect
✅ CurrencyInput

Display
✅ DetailRow

Modal
✅ BaseModal
✅ ConfirmDeleteModal

Utils
✅ formatter.js

resources
└── js
    └── Components
        ├── Button
        │   ├── BaseButton.vue
        │   └── ButtonGroup.vue
        │
        ├── Display
        │   └── DetailRow.vue
        │
        ├── Feedback
        │   ├── BaseToast.vue
        │   ├── EmptyState.vue ⭐
        │   └── BaseBadge.vue ⭐
        │
        ├── Form
        │   ├── FormField.vue
        │   ├── FormSection.vue
        │   ├── CurrencyInput.vue
        │   └── SearchableSelect.vue
        │
        ├── Layout
        │   ├── Card.vue
        │   ├── PageHeader.vue
        │   ├── ActionBar.vue
        │   └── DataTableToolbar.vue ⭐
        │
        ├── Modal
        │   ├── BaseModal.vue
        │   └── ConfirmDeleteModal.vue
        │
        ├── Table ⭐
        │   ├── DataTable.vue
        │   ├── TableToolbar.vue
        │   ├── TablePagination.vue
        │   ├── TableEmpty.vue
        │   └── TableLoading.vue
        │
        └── UI