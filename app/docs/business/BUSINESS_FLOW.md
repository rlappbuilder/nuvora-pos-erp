# ==========================================================
# NUVORA ERP
# BUSINESS FLOW
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjelaskan alur bisnis
seluruh modul Nuvora ERP.

Setiap modul WAJIB mengikuti
Business Flow yang telah ditentukan.

Tidak diperbolehkan membuat
alur transaksi berbeda
tanpa persetujuan.

---

# ERP PRINCIPLE

Master Data

↓

Transaction

↓

Approval

↓

Posting

↓

Reporting

↓

Closing

Semua transaksi ERP
WAJIB mengikuti alur tersebut.

---

# ==========================================================
# FOUNDATION
# ==========================================================

Company

↓

Branch

↓

User

↓

Role

↓

Permission

↓

Login

Saat Login

System otomatis mengambil

✓ Company

✓ Branch

✓ Role

✓ Permission

User

TIDAK memilih Branch.

System menentukan secara otomatis.

---

# DATA SCOPE

Setiap User memiliki

Company

Branch

Role

Scope

Contoh

Kasir Bandung

↓

Company

PT Nuvora

↓

Branch

Bandung

↓

Role

Cashier

↓

Scope

Branch

Semua transaksi otomatis

Company = PT Nuvora

Branch = Bandung

---

# ==========================================================
# MASTER DATA
# ==========================================================

Semua Master Data

Create

↓

Edit

↓

Active

↓

Inactive

↓

Restore

↓

Archive

Master Data

Tidak boleh langsung dihapus.

Menggunakan

SoftDelete.

---

# CASH BANK FLOW

Cash Bank

↓

Opening Balance

↓

Cash In

↓

Cash Out

↓

Transfer

↓

Adjustment

↓

Closing

↓

Reporting

Current Balance

selalu dihitung

berdasarkan transaksi.

Bukan diinput manual.

---

# CUSTOMER FLOW

Customer

↓

Quotation

↓

Sales Order

↓

Delivery

↓

Invoice

↓

Payment

↓

Ledger

↓

Report

---

# SUPPLIER FLOW

Supplier

↓

Purchase Request

↓

Purchase Order

↓

Goods Receipt

↓

Supplier Invoice

↓

Supplier Payment

↓

Ledger

↓

Report

---

# PRODUCT FLOW

Category

↓

Brand

↓

Product

↓

Warehouse

↓

Opening Stock

↓

Stock Movement

↓

Stock Adjustment

↓

Stock Opname

↓

Inventory Report

---

# SALES FLOW

Quotation

↓

Sales Order

↓

Delivery Order

↓

Invoice

↓

Payment

↓

Journal

↓

Ledger

↓

Financial Report

---

# PURCHASE FLOW

Purchase Request

↓

RFQ

↓

Purchase Order

↓

Goods Receipt

↓

Supplier Invoice

↓

Payment

↓

Journal

↓

Ledger

---

# INVENTORY FLOW

Opening Stock

↓

Purchase

↓

Transfer

↓

Production

↓

Sales

↓

Adjustment

↓

Stock Opname

↓

Closing

---

# POS FLOW

Open Session

↓

Open Shift

↓

Cash In

↓

Sales

↓

Payment

↓

Print Receipt

↓

Cash Out

↓

Closing Shift

↓

Cash Count

↓

Difference

↓

Posting

↓

Journal

---

# ACCOUNTING FLOW

Opening Balance

↓

Journal

↓

Posting

↓

Ledger

↓

Trial Balance

↓

Financial Statement

↓

Closing Period

---

# JOURNAL FLOW

Draft

↓

Approved

↓

Posted

↓

Locked

↓

Reversal (optional)

Posted Journal

Tidak boleh diubah.

Harus dibuat

Reverse Journal.

---

# PAYMENT FLOW

Invoice

↓

Payment

↓

Cash Bank

↓

Journal

↓

Ledger

↓

Report

---

# STOCK FLOW

Opening

↓

In

↓

Out

↓

Transfer

↓

Adjustment

↓

Closing

Semua Stock Movement

WAJIB mempunyai

Audit Trail.

---

# APPROVAL FLOW

Draft

↓

Submitted

↓

Approved

↓

Posted

↓

Completed

atau

↓

Rejected

Approval

tidak boleh dilewati.

---

# REPORT FLOW

Transaction

↓

Posting

↓

Ledger

↓

Report

↓

Dashboard

Semua Report

mengambil data

dari transaksi

yang sudah Posted.

---

# MULTI COMPANY

Owner

↓

Company A

↓

Branch A

↓

Warehouse

↓

Cash Bank

Owner juga dapat

Company B

Branch B

Warehouse

Cash Bank

Semua data

terpisah.

---

# MULTI BRANCH

Company

↓

Branch

↓

Warehouse

↓

Cash Bank

↓

User

↓

Transaction

Semua transaksi

WAJIB mempunyai

Branch.

---

# USER LOGIN FLOW

Login

↓

Authentication

↓

Load User

↓

Load Role

↓

Load Permission

↓

Load Company

↓

Load Branch

↓

Dashboard

Session User

menyimpan

company_id

branch_id

role_id

permission

---

# SECURITY FLOW

Login

↓

Permission Check

↓

Data Scope Check

↓

Business Rule Check

↓

Database

User

tidak boleh

langsung mengakses

Company lain

atau Branch lain

di luar Scope.

---

# AUDIT FLOW

Create

↓

Update

↓

Delete

↓

Restore

↓

Approval

↓

Posting

↓

Login

↓

Logout

Semua aktivitas

dicatat

Activity Log.

---

# ERP RULES

Semua transaksi

WAJIB mempunyai

✓ Company

✓ Branch

✓ User

✓ Date

✓ Status

✓ Audit

Tidak ada transaksi

tanpa Company

atau Branch.

---

# STATUS

Draft

↓

Submitted

↓

Approved

↓

Posted

↓

Completed

↓

Archived

---

# ERP PRINCIPLE

Input

↓

Validation

↓

Approval

↓

Posting

↓

Reporting

↓

Closing

Tidak boleh

langsung

Input

↓

Report

Tanpa proses Posting.

---

# Version

1.0

Status

Approved

Frozen