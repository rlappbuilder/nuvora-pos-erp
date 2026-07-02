# ==========================================================
# NUVORA ERP
# PERMISSION MATRIX
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar Role Based Access Control (RBAC)
untuk seluruh modul Nuvora ERP.

Seluruh menu, tombol, aksi, laporan,
dan API WAJIB menggunakan Permission.

Tidak diperbolehkan melakukan pengecekan
role secara langsung di Controller atau Vue.

Gunakan Permission.

---

# Architecture

User

↓

Role

↓

Permission

↓

Module

↓

Action

---

# Default Roles

Super Administrator

Administrator

Owner

Manager

Supervisor

Accountant

Finance

Purchasing

Warehouse

Sales

Cashier

HR

Auditor

Viewer

---

# Standard Action

Setiap module WAJIB memiliki permission berikut

View

Create

Edit

Delete

Restore

Approve

Reject

Print

Export

Import

Post

Unpost

Lock

Unlock

Close

Reopen

---

# Permission Naming

Gunakan format

module.action

Contoh

company.view

company.create

company.edit

company.delete

branch.view

branch.create

cash-bank.view

cash-bank.create

cash-bank.edit

cash-bank.delete

customer.view

supplier.view

product.view

purchase.view

sales.view

journal.post

journal.unpost

---

# ==========================================================
# FOUNDATION
# ==========================================================

Authentication

login

logout

change-password

User

user.view

user.create

user.edit

user.delete

user.restore

Role

role.view

role.create

role.edit

role.delete

Permission

permission.view

permission.create

permission.edit

permission.delete

Company

company.view

company.create

company.edit

company.delete

Branch

branch.view

branch.create

branch.edit

branch.delete

---

# ==========================================================
# MASTER DATA
# ==========================================================

Cash Bank

cash-bank.view

cash-bank.create

cash-bank.edit

cash-bank.delete

cash-bank.restore

cash-bank.export

cash-bank.import

Warehouse

warehouse.view

warehouse.create

warehouse.edit

warehouse.delete

Customer

customer.view

customer.create

customer.edit

customer.delete

Supplier

supplier.view

supplier.create

supplier.edit

supplier.delete

Product

product.view

product.create

product.edit

product.delete

Category

category.view

category.create

category.edit

category.delete

Brand

brand.view

brand.create

brand.edit

brand.delete

Unit

unit.view

unit.create

unit.edit

unit.delete

Currency

currency.view

currency.create

currency.edit

currency.delete

Tax

tax.view

tax.create

tax.edit

tax.delete

Payment Term

payment-term.view

payment-term.create

payment-term.edit

payment-term.delete

Chart Of Accounts

coa.view

coa.create

coa.edit

coa.delete

coa.export

---

# ==========================================================
# INVENTORY
# ==========================================================

stock.view

stock.adjustment

stock.transfer

stock.opname

stock.opening

stock.export

stock.import

---

# ==========================================================
# PURCHASE
# ==========================================================

purchase-request.view

purchase-request.create

purchase-request.approve

purchase-order.view

purchase-order.create

purchase-order.approve

purchase-order.cancel

goods-receipt.view

goods-receipt.create

purchase-return.view

supplier-payment.view

supplier-payment.create

---

# ==========================================================
# SALES
# ==========================================================

quotation.view

quotation.create

sales-order.view

sales-order.create

sales-order.approve

delivery-order.view

delivery-order.create

sales-return.view

customer-payment.view

customer-payment.create

---

# ==========================================================
# POINT OF SALE
# ==========================================================

pos.open-session

pos.close-session

pos.create-order

pos.cancel-order

pos.cash-in

pos.cash-out

pos.shift-close

pos.print-receipt

---

# ==========================================================
# ACCOUNTING
# ==========================================================

journal.view

journal.create

journal.edit

journal.delete

journal.post

journal.unpost

ledger.view

trial-balance.view

profit-loss.view

balance-sheet.view

cash-flow.view

bank-reconciliation.view

asset.view

asset.depreciation

closing-period

budget.view

cost-center.view

---

# ==========================================================
# REPORT
# ==========================================================

report.sales

report.purchase

report.inventory

report.accounting

report.tax

report.audit

dashboard.executive

dashboard.operational

---

# Scope Permission

Selain Action,

setiap permission mempunyai Scope.

Global

↓

Company

↓

Branch

↓

Own

Contoh

Finance Cabang Bandung

hanya dapat melihat

Cash Bank

Cabang Bandung.

---

# Data Visibility

User hanya dapat melihat data

sesuai

Company

Branch

Role

Permission

---

# Frontend

Vue

Gunakan

v-can

atau

hasPermission()

Contoh

<button

v-if="

hasPermission(

'cash-bank.create'

)

"

>

Create

</button>

Jangan hardcode

Role.

---

# Backend

Gunakan

Policy

Gate

Middleware

Contoh

$this->authorize(

'cash-bank.create'

);

---

# API

Seluruh API

WAJIB menggunakan

Permission Middleware.

---

# Audit

Seluruh perubahan Permission

WAJIB dicatat

Activity Log.

---

# Super Administrator

Memiliki seluruh permission.

Tidak boleh dibatasi.

---

# Viewer

Hanya

View

Print

Export

Tidak dapat

Create

Edit

Delete

Approve

---

# Approval

Approval

tidak boleh

menggunakan

Permission Create.

Approval

harus mempunyai

permission sendiri.

Contoh

purchase-order.approve

journal.post

sales-order.approve

---

# ERP Standard

Seluruh module baru

WAJIB menambahkan

Permission

ke dokumen ini.

---

# Status

Approved

Version

1.0

Frozen