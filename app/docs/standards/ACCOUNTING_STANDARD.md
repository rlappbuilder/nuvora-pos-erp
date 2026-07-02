# ==========================================================
# NUVORA ERP
# ACCOUNTING STANDARD
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar
Accounting Engine
Nuvora ERP.

Seluruh transaksi yang mempengaruhi
keuangan WAJIB mengikuti standar ini.

Tidak diperbolehkan
mengubah saldo secara manual.

Saldo hanya berubah
karena transaksi yang sudah Posted.

---

# Accounting Principle

Business Transaction

↓

Validation

↓

Approval

↓

Posting

↓

Journal

↓

Ledger

↓

Financial Report

Semua laporan keuangan

berasal dari Journal.

Bukan dari tabel transaksi.

---

# Double Entry

Seluruh transaksi WAJIB menggunakan

Double Entry Accounting.

Debit

=

Credit

Tidak boleh ada

Unbalance Journal.

---

# Posting System

Draft

↓

Approved

↓

Posted

↓

Locked

Setelah Posted

tidak boleh diedit.

Jika salah

buat Reverse Journal.

---

# Cash Flow

Cash In

↓

Journal

Debit

Cash

Credit

Source Account

Cash Out

↓

Journal

Debit

Expense

Credit

Cash

Transfer

↓

Journal

Debit

Cash Destination

Credit

Cash Source

---

# Sales

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

Posting

↓

Journal

↓

General Ledger

---

# Purchase

Purchase Request

↓

Purchase Order

↓

Goods Receipt

↓

Supplier Invoice

↓

Payment

↓

Posting

↓

Journal

↓

General Ledger

---

# Inventory

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

Closing

↓

Inventory Valuation

↓

Journal

---

# Automatic Journal

Seluruh transaksi

WAJIB membuat

Journal otomatis.

Tidak boleh

Input Journal Manual

untuk transaksi biasa.

---

# Manual Journal

Hanya digunakan

untuk

Adjustment

Correction

Opening Balance

Closing

Depreciation

Accrual

---

# Chart Of Accounts

Seluruh transaksi

WAJIB mempunyai

COA.

Tidak boleh

Cash Bank

tanpa COA.

Warehouse

tidak mempunyai COA.

Inventory Account

berasal dari Product Category.

---

# Journal Status

Draft

Approved

Posted

Reversed

Cancelled

Locked

---

# Fiscal Year

Semua transaksi

WAJIB berada

dalam Fiscal Year.

---

# Fiscal Period

Monthly

Quarterly

Yearly

Closing Period

tidak dapat menerima

transaksi baru.

---

# Closing

Month End

↓

Year End

↓

Retained Earnings

↓

Opening Balance

---

# Opening Balance

Hanya dapat dilakukan

sekali

per Fiscal Year.

---

# General Ledger

Seluruh Journal

otomatis masuk

General Ledger.

Tidak ada input

Ledger manual.

---

# Trial Balance

Diambil

dari

General Ledger.

---

# Balance Sheet

Diambil

dari

General Ledger.

---

# Profit Loss

Diambil

dari

General Ledger.

---

# Cash Flow

Diambil

dari

Cash Journal.

Tidak dihitung

manual.

---

# Bank Reconciliation

Bank Statement

↓

Matching

↓

Reconciliation

↓

Completed

---

# Fixed Asset

Purchase

↓

Capitalization

↓

Depreciation

↓

Disposal

↓

Journal

---

# Cost Center

Setiap transaksi

boleh mempunyai

Cost Center.

Contoh

Marketing

Production

HR

IT

Sales

---

# Department

Optional

Digunakan

untuk

analisa laporan.

---

# Multi Currency

Seluruh Journal

mempunyai

Currency

Exchange Rate

Base Currency

---

# Exchange Rate

Disimpan

pada saat Posting.

Tidak mengikuti

kurs terbaru.

---

# Audit

Seluruh Journal

WAJIB mempunyai

Created By

Posted By

Approved By

Reversed By

Date

Time

---

# Lock Journal

Journal Posted

tidak boleh

Update

Delete

Hanya

Reverse.

---

# Reverse Journal

Journal Lama

↓

Reverse

↓

Journal Baru

↓

Audit

---

# Accounting Formula

Assets

=

Liabilities

+

Equity

Selalu Balance.

---

# Inventory Valuation

Support

FIFO

Moving Average

LIFO

(LIFO hanya jika diaktifkan oleh perusahaan)

Default

Moving Average.

---

# HPP

Mengikuti

Inventory Valuation.

---

# Tax

Output Tax

Input Tax

PPN

PPH

Tax Journal

otomatis.

---

# Budget

Budget

↓

Actual

↓

Variance

---

# Approval

Nominal tertentu

WAJIB Approval.

Contoh

> 50 juta

↓

Manager

↓

Finance

↓

Director

Nilai Approval

dapat diatur

melalui Setting.

---

# ERP Principle

Tidak ada

Update Saldo.

Saldo

selalu hasil

perhitungan Journal.

---

# Formula

Current Balance

=

Opening Balance

+

Debit

-

Credit

---

# Report

Balance Sheet

Profit Loss

Trial Balance

General Ledger

Cash Flow

Journal Report

Tax Report

Budget Report

Semua report

berasal dari Journal.

---

# Accounting Rules

✓ Double Entry

✓ Journal Automatic

✓ Posting

✓ Approval

✓ Audit Trail

✓ Fiscal Year

✓ Fiscal Period

✓ Multi Company

✓ Multi Branch

✓ Multi Currency

✓ Cost Center

✓ Department

✓ Reverse Journal

✓ Closing

✓ Reconciliation

---

# Status

Approved

Frozen

Version

1.0