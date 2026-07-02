# ==========================================================
# NUVORA ERP
# UI / UX GUIDELINE
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar
User Interface (UI)
dan
User Experience (UX)

untuk seluruh modul
Nuvora ERP.

Semua halaman WAJIB mengikuti
guideline ini.

---

# DESIGN PRINCIPLE

Simple

Professional

Enterprise

Consistent

Fast

Minimal Click

Responsive

Accessibility First

Odoo Inspired

---

# COLOR SYSTEM

Primary

Indigo

Success

Green

Warning

Amber

Danger

Red

Info

Blue

Neutral

Gray

Seluruh warna
menggunakan Tailwind.

Tidak boleh hardcode
hex color
di Component.

---

# BORDER RADIUS

Input

rounded-xl

Card

rounded-2xl

Dialog

rounded-2xl

Button

rounded-xl

Menggunakan

DesignSystem.js

---

# SPACING

Small

8px

Medium

16px

Large

24px

Extra Large

32px

Seluruh spacing
menggunakan token
DesignSystem.

---

# TYPOGRAPHY

Heading

text-2xl

font-bold

Section Title

text-lg

font-semibold

Label

text-sm

font-medium

Content

text-sm

Hint

text-xs

Error

text-sm

---

# PAGE LAYOUT

Page Header

↓

Breadcrumb

↓

Toolbar

↓

Summary Card

↓

Filter

↓

Table / Form

↓

Pagination

Semua halaman
mengikuti urutan ini.

---

# FORM STANDARD

Semua Form

WAJIB menggunakan

PageHeader

FormSection

FormInput

NumberInput

CurrencyInput

SearchableSelect

ValidationError

Tidak diperbolehkan
membuat Input manual.

---

# FORM SECTION

General

Organization

Financial

Accounting

Other Information

Urutan Section
WAJIB konsisten.

---

# BUTTON POSITION

Save

Primary

kanan bawah

Cancel

Secondary

kanan bawah

Delete

Danger

kanan bawah

Back

kiri atas

---

# TABLE STANDARD

Search

Filter

Export

Import

Refresh

Column Setting

Pagination

Semua berada
di bagian atas tabel.

---

# SEARCH

Search selalu
berada di kiri.

Button Action

berada di kanan.

---

# PAGINATION

Default

20 rows

Pilihan

20

50

100

200

---

# SUMMARY CARD

Selalu berada
di atas tabel.

Maksimal

4 Card

Gunakan

Format Ringkas

Rp 1,2 M

Tooltip

menampilkan nilai penuh.

---

# EMPTY STATE

Jika data kosong

WAJIB menampilkan

Illustration

Title

Description

Button Create

Tidak boleh
menampilkan
tabel kosong.

---

# ICON

Gunakan

Heroicons

Ukuran

20px

atau

24px

---

# MODAL

Gunakan

Rounded XL

Shadow

Background Blur

ESC Close

Click Outside

Optional

---

# DIALOG

Delete

Warning

Save

Success

Confirmation

Semua menggunakan

SweetAlert2.

---

# NOTIFICATION

Success

Hijau

Error

Merah

Warning

Kuning

Info

Biru

Durasi

3 detik

---

# LOADING

Gunakan

Skeleton

atau

Spinner

Tidak boleh
blank screen.

---

# RESPONSIVE

Desktop

Tablet

Mobile

Semua halaman
WAJIB responsive.

---

# ACCESSIBILITY

Keyboard Navigation

Tab Support

Enter Support

ESC Support

Focus Ring

ARIA Label

---

# SEARCHABLE SELECT

Search

Keyboard

Arrow

Enter

ESC

Loading

Empty State

Max Height

Virtual Scroll

(untuk data besar)

---

# CURRENCY

Gunakan

Format Ringkas

Rp 1,2 Jt

Rp 1,3 M

Rp 2,1 T

Tooltip

nilai lengkap.

---

# DATE

Format

dd MMM yyyy

Contoh

24 Jul 2025

---

# DATETIME

24 Jul 2025

14:35

---

# STATUS BADGE

Hijau

Active

Merah

Inactive

Kuning

Pending

Biru

Posted

Abu

Draft

---

# DELETE

Tidak langsung delete.

Selalu tampil

Confirmation Dialog.

Jika menggunakan Soft Delete,
tampilkan opsi Restore.

---

# SAVE

Setelah Save

Tampilkan

Success Dialog

↓

Add More

atau

Back To List

---

# DASHBOARD

Summary Card

↓

Chart

↓

Recent Transaction

↓

Activity

↓

Quick Action

---

# DARK MODE

Semua Component

WAJIB siap

Dark Mode

meskipun belum diaktifkan.

---

# PERFORMANCE

Lazy Load

Virtual Scroll

Debounce

Caching

Code Splitting

---

# CONSISTENCY

Satu fungsi

Satu tampilan.

Tidak boleh

Customer

berbeda

dengan

Supplier.

---

# ERP RULE

User

tidak boleh berpikir

"menu ini berbeda."

Semua module

harus terasa

satu aplikasi.

---

# STATUS

Approved

Version

1.0

Frozen