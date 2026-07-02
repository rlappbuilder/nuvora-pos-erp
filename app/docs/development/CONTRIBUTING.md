# ==========================================================
# NUVORA ERP
# CONTRIBUTING GUIDE
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi panduan bagi seluruh developer
yang berkontribusi pada Nuvora ERP.

Semua developer WAJIB membaca dokumen ini
sebelum melakukan perubahan kode.

---

# Development Workflow

Issue

↓

Discussion

↓

Planning

↓

Development

↓

Testing

↓

Code Review

↓

Merge

↓

Release

---

# Branch Strategy

main

Production

develop

Development

feature/*

New Feature

bugfix/*

Bug Fix

hotfix/*

Production Fix

release/*

Release Candidate

---

# Naming Branch

feature/cash-bank

feature/customer

feature/product

bugfix/search-company

hotfix/login-error

release/v1.0.0

---

# Commit Standard

Gunakan Conventional Commit.

feat:

fix:

refactor:

docs:

style:

perf:

test:

build:

ci:

---

# Example

feat(accounting): add journal posting

fix(cash-bank): validation company

docs(database): update standard

refactor(form): simplify input component

---

# Before Commit

✓ Composer Test

✓ npm build

✓ Linter

✓ Unit Test

✓ Feature Test

✓ No Debug Code

---

# Forbidden

❌ dd()

❌ dump()

❌ console.log()

❌ var_dump()

❌ hardcode credentials

❌ commented dead code

---

# Pull Request Checklist

✓ Description

✓ Screenshot

✓ Database Migration

✓ Testing Result

✓ Breaking Change

✓ Documentation Updated

---

# Coding Rules

Ikuti

CODING_STANDARD.md

DATABASE_STANDARD.md

API_STANDARD.md

UIKIT_SPEC.md

---

# Database Change

Jika migration baru

WAJIB update

DATABASE_STANDARD.md

---

# UI Change

Jika Component berubah

WAJIB update

UIKIT_SPEC.md

---

# New Module

Jika module baru

WAJIB update

MODULE_ROADMAP.md

PERMISSION_MATRIX.md

BUSINESS_FLOW.md

---

# Review Checklist

Readable

Reusable

Secure

Responsive

Performance

Documentation

Testing

---

# Definition of Done

✓ Feature selesai

✓ Test lulus

✓ Review Approved

✓ Documentation Updated

✓ Ready Release

---

# Status

Approved

Frozen