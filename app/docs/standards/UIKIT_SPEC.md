# ==========================================================
# NUVORA ERP
# UI KIT SPECIFICATION
# Version : 1.0
# Status  : Draft
# ==========================================================

## Objective

UI Kit adalah kumpulan reusable component yang digunakan
oleh seluruh modul Nuvora ERP.

Semua halaman Create, Edit, Filter, Search, Dialog,
Wizard dan Transaction WAJIB menggunakan UI Kit ini.

Tidak diperbolehkan membuat komponen baru apabila
fungsi yang sama sudah tersedia di UI Kit.

---

# Design Principle

- Clean
- Enterprise
- Responsive
- Accessible
- Reusable
- Odoo Inspired
- TailwindCSS
- Vue 3 Composition API

---

# Component Rules

Semua Component WAJIB memiliki:

✓ v-model

✓ update:modelValue

✓ label

✓ placeholder

✓ hint

✓ required

✓ disabled

✓ readonly

✓ error

✓ size

✓ dark mode ready

✓ responsive

✓ validation support

---

# Folder Structure

resources/

└── js/

    └── Components/

        └── Form/

            FormInput.vue

            NumberInput.vue

            CurrencyInput.vue

            SearchableSelect.vue

            FormTextarea.vue

            FormSection.vue

            DatePicker.vue

            DateTimePicker.vue

            Checkbox.vue

            RadioGroup.vue

            Toggle.vue

            FileUpload.vue

            ValidationError.vue

            PageHeader.vue

---

# Design System

Semua component WAJIB mengambil style dari

DesignSystem.js

Dilarang hardcode Tailwind Class yang sudah tersedia
di DesignSystem.

---

# Component Status

| Component | Status |
|-----------|---------|
| ValidationError | Approved |
| FormInput | Draft |
| NumberInput | Draft |
| CurrencyInput | Draft |
| SearchableSelect | Draft |
| FormTextarea | Draft |
| FormSection | Draft |
| PageHeader | Draft |

---

# Naming Convention

Props menggunakan camelCase

Event menggunakan kebab-case

Semua component menggunakan

<script setup>

---

# Validation

Semua validation menggunakan

ValidationError.vue

Tidak boleh membuat div error manual.

---

# Styling

Border Radius

DesignSystem.Radius

Spacing

DesignSystem.InputSize

Border

DesignSystem.NormalClass

Error

DesignSystem.ErrorClass

Hint

DesignSystem.HintClass

Label

DesignSystem.LabelClass

---

# Component Lifecycle

Draft

↓

Testing

↓

Approved

↓

Frozen

Component yang sudah Frozen

TIDAK boleh diubah

kecuali Major Version.

---

# Version

UI Kit v1.0

Target

Stable

Reusable

Enterprise Ready