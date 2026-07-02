# ==========================================================
# NUVORA ERP
# API STANDARD
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar seluruh API
yang digunakan oleh Nuvora ERP.

Baik API Internal maupun External
WAJIB mengikuti standar ini.

---

# API Style

REST API

JSON

UTF-8

HTTPS

Stateless

---

# URL Standard

Gunakan

/api/

Contoh

GET

/api/companies

GET

/api/customers

POST

/api/customers

PUT

/api/customers/{id}

DELETE

/api/customers/{id}

---

# Resource Naming

Gunakan

Plural

Contoh

companies

branches

customers

suppliers

cash-banks

warehouses

purchase-orders

sales-orders

journal-entries

---

# HTTP Method

GET

Mengambil data

POST

Menyimpan data

PUT

Update data

PATCH

Partial Update

DELETE

Soft Delete

---

# Response Format

Semua response WAJIB menggunakan format berikut

{

    "success": true,

    "message": "Customer created successfully.",

    "data": {}

}

---

# Error Response

{

    "success": false,

    "message": "Validation Error",

    "errors": {}

}

---

# HTTP Status

200

Success

201

Created

204

No Content

400

Bad Request

401

Unauthorized

403

Forbidden

404

Not Found

409

Conflict

422

Validation Error

500

Internal Server Error

---

# Validation Error

{

    "success": false,

    "message": "Validation Error",

    "errors": {

        "name": [

            "Name is required."

        ]

    }

}

---

# Pagination

Semua endpoint list

WAJIB mendukung

page

per_page

search

sort

direction

Contoh

GET

/api/customers

?page=1

&per_page=20

&search=reno

&sort=name

&direction=asc

---

# Filtering

Gunakan Query String

Contoh

?company_id=1

?branch_id=3

?status=active

?type=cash

?from=2025-01-01

?to=2025-12-31

---

# Sorting

Gunakan

sort

direction

Contoh

sort=name

direction=asc

---

# Searching

Gunakan

search

Contoh

?search=kas

---

# Date Format

Gunakan

ISO 8601

Example

2025-08-01T14:30:00Z

Frontend

boleh menampilkan

dd/mm/yyyy

Backend

tetap ISO.

---

# Boolean

Gunakan

true

false

Jangan

1

0

Pada JSON Response.

---

# Decimal

Seluruh nominal

WAJIB dikirim

dalam bentuk Number

Contoh

125000.50

Bukan

"125000.50"

---

# Relationship

Gunakan

nested object

Contoh

{

    "company": {

        "id": 1,

        "name": "PT Nuvora"

    }

}

Bukan

company_name

company_code

terpisah.

---

# Authentication

Gunakan

Laravel Sanctum

Untuk seluruh API.

---

# Authorization

Gunakan

Policy

Gate

Permission

Tidak melakukan pengecekan role

langsung di Controller.

---

# API Version

Gunakan

v1

Contoh

/api/v1/customers

Jika nanti ada breaking change

buat

v2

Tanpa merusak

v1.

---

# Upload

Gunakan

multipart/form-data

Response

{

    "success": true,

    "message": "File uploaded successfully.",

    "data": {

        "url": "...",

        "path": "..."

    }

}

---

# Download

Gunakan

GET

/download/{id}

Jangan

POST

untuk download.

---

# Soft Delete

DELETE

Tidak menghapus data permanen.

Gunakan

SoftDeletes.

Restore

POST

/{id}/restore

Force Delete

DELETE

/{id}/force

---

# Bulk Operation

Gunakan endpoint khusus.

POST

/customers/bulk-delete

POST

/customers/bulk-update

POST

/customers/import

GET

/customers/export

---

# API Naming

Gunakan

camelCase

untuk JSON Property.

Contoh

openingBalance

currentBalance

createdAt

updatedAt

Catatan:

Database tetap menggunakan

snake_case.

---

# Rate Limiting

Default

60 request / minute

Untuk Public API.

Internal API

mengikuti middleware Laravel.

---

# Logging

Seluruh Error API

WAJIB dicatat

di Laravel Log.

Request sensitif

(password, token)

tidak boleh dicatat.

---

# Documentation

Seluruh endpoint

WAJIB memiliki dokumentasi.

Minimal berisi:

- URL
- Method
- Request
- Response
- Validation
- Example

---

# Security

Semua input

WAJIB divalidasi.

Gunakan

FormRequest

atau

Validator.

Jangan mempercayai

data dari client.

---

# Performance

Gunakan

Pagination

Caching

Eager Loading

Queue

untuk proses berat.

---

# ERP Standard

Seluruh endpoint Master Data

WAJIB mendukung:

✓ Search

✓ Filter

✓ Sort

✓ Pagination

✓ Active Only

✓ Company

✓ Branch

---

# Status

Approved

Frozen

Tidak boleh diubah

kecuali Major Version.