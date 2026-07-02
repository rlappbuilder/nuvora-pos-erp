# ==========================================================
# NUVORA ERP
# DATABASE STANDARD
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar perancangan database
untuk seluruh modul Nuvora ERP.

Seluruh tabel WAJIB mengikuti standar ini.

Tidak diperbolehkan membuat struktur database
yang berbeda tanpa alasan yang jelas.

---

# Database Engine

MySQL 8+

Charset

utf8mb4

Collation

utf8mb4_unicode_ci

Storage Engine

InnoDB

Timezone

UTC

---

# Primary Key

Semua tabel WAJIB menggunakan

id

unsignedBigInteger

Laravel

$table->id();

---

# Foreign Key

Gunakan

foreignId()

constrained()

cascadeOnUpdate()

restrictOnDelete()

Contoh

$table

    ->foreignId(

        'company_id'

    )

    ->constrained()

    ->cascadeOnUpdate()

    ->restrictOnDelete();

---

# Audit Columns

Seluruh Master Data WAJIB memiliki

created_by

updated_by

deleted_by

timestamps

softDeletes

Contoh

$table->foreignId(

'created_by'

)->nullable();

$table->foreignId(

'updated_by'

)->nullable();

$table->foreignId(

'deleted_by'

)->nullable();

$table->timestamps();

$table->softDeletes();

---

# Transaction Table

Seluruh tabel transaksi WAJIB memiliki

created_by

updated_by

timestamps

SoftDeletes

HANYA digunakan jika memang diperlukan.

---

# Company Structure

Seluruh Master Data WAJIB mendukung

Multi Company

Gunakan

company_id

Jika memang tidak relevan

berikan alasan pada dokumentasi.

---

# Branch Structure

Seluruh tabel transaksi

WAJIB mempunyai

branch_id

kecuali memang Global.

Contoh

Sales

Purchase

Inventory

CashBank

Warehouse

Journal

Payment

Stock

Invoice

Semua mempunyai

branch_id

---

# Naming Convention

Table

snake_case

Plural

Example

companies

branches

cash_banks

purchase_orders

sales_orders

stock_movements

Columns

snake_case

Singular

Example

company_id

branch_id

customer_id

supplier_id

warehouse_id

---

# Boolean

Gunakan

is_

Prefix

Example

is_active

is_default

is_locked

is_system

is_cash

is_bank

---

# Date Column

Gunakan

_at

Suffix

Example

posted_at

approved_at

paid_at

received_at

expired_at

cancelled_at

closed_at

---

# Money

Seluruh nominal

WAJIB menggunakan

decimal

19,4

Contoh

$table

->decimal(

'opening_balance',

19,

4

);

Jangan menggunakan

float

double

---

# Quantity

Gunakan

decimal

19,4

Contoh

qty

received_qty

issued_qty

remaining_qty

---

# Percentage

Gunakan

decimal

8,4

Contoh

discount

tax_rate

commission

---

# Status

Status aktif

Gunakan

is_active

boolean

Bukan

status integer

Workflow Status

Gunakan

enum

atau

varchar

Contoh

Draft

Submitted

Approved

Rejected

Posted

Cancelled

Completed

---

# Description

Gunakan

description

longText

Bukan

remarks

notes

comment

Gunakan satu nama

description

untuk konsistensi.

---

# Code

Seluruh Master Data

WAJIB mempunyai

code

unique

Contoh

CB000001

CUS000001

SUP000001

ITEM000001

COA000001

---

# Name

Gunakan

name

Sebagai nama utama.

Jangan membuat

customer_name

supplier_name

warehouse_name

company_name

Jika memang entity tersebut

sudah jelas.

Contoh

companies

id

code

name

Bukan

company_name

---

# Address

Gunakan

address

city

province

postal_code

country

---

# Contact

Gunakan

phone

mobile

email

website

---

# Index

WAJIB membuat index

untuk

company_id

branch_id

code

name

is_active

deleted_at

created_at

---

# Composite Index

Gunakan

company_id

+

branch_id

Jika sering digunakan

bersamaan.

Contoh

$table->index([

'company_id',

'branch_id'

]);

---

# Unique

Gunakan

unique

untuk

code

email

username

barcode

account_number

---

# JSON

Gunakan JSON

hanya untuk

Dynamic Configuration

Settings

Metadata

Jangan menyimpan

data transaksi

dalam JSON.

---

# Pivot Table

Gunakan

Alphabetical Order

Contoh

permission_role

product_supplier

role_user

---

# Migration

Nama migration

harus jelas

Contoh

create_cash_banks_table

create_customers_table

add_branch_to_cash_banks_table

add_company_to_suppliers_table

---

# Seeder

Urutan

Company

↓

Branch

↓

Role

↓

Permission

↓

User

↓

Master Data

↓

Transaction

---

# Soft Delete

Master Data

WAJIB

SoftDeletes

Transaction

Default

Tidak menggunakan

SoftDeletes

kecuali memang dibutuhkan.

---

# Deletion

Semua Master Data

menggunakan

deleted_by

deleted_at

Delete Reason

(optional)

Untuk audit.

---

# Relationship

Gunakan

belongsTo

hasMany

hasOne

belongsToMany

Jangan membuat

Query Manual

jika bisa memakai

Relationship.

---

# Lazy Loading

Dilarang

Menggunakan

Lazy Loading

Gunakan

with()

load()

Eager Loading

untuk mencegah

N+1 Query.

---

# Enum

Gunakan Enum

untuk

Type

Status

Category

Movement

Direction

Approval

Contoh

CashBankType

JournalStatus

StockMovementType

---

# Repository

Query kompleks

WAJIB dipindahkan

ke Repository

atau Service.

---

# Code Generator

Seluruh penomoran

WAJIB menggunakan

CodeGeneratorService

Tidak boleh

generate

langsung di Controller.

---

# ERP Standard

Semua Master Data

minimal mempunyai

id

company_id

branch_id

code

name

description

is_active

created_by

updated_by

deleted_by

created_at

updated_at

deleted_at

---

# Documentation

Jika membuat tabel baru

WAJIB update

DATABASE_STANDARD.md

MODULE_ROADMAP.md

CHANGELOG.md

---

# Status

Approved

Frozen

Tidak boleh diubah

kecuali Major Version.