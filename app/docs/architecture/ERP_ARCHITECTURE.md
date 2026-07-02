# ==========================================================
# NUVORA ERP
# ERP ARCHITECTURE
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi blueprint teknis
seluruh arsitektur Nuvora ERP.

Semua developer WAJIB mengikuti
arsitektur ini.

Tidak diperbolehkan membuat
alur baru tanpa mengikuti
Architecture Layer.

---

# OVERVIEW

                User

                  │

                  ▼

          Vue 3 + Inertia

                  │

                  ▼

            Laravel Controller

                  │

                  ▼

             Form Request

                  │

                  ▼

              Service Layer

                  │

                  ▼

          Repository Layer

                  │

                  ▼

          Eloquent / Database

                  │

                  ▼

               MySQL

---

# LAYER

Presentation Layer

↓

Application Layer

↓

Domain Layer

↓

Infrastructure Layer

↓

Database

---

# PRESENTATION

Vue 3

Inertia

Tailwind

Heroicons

SweetAlert

ApexCharts

Semua UI
berada pada layer ini.

Tidak boleh

Business Logic.

---

# APPLICATION

Controller

FormRequest

Middleware

Policy

Gate

Controller hanya

Request

↓

Validation

↓

Service

↓

Response

---

# DOMAIN

Service

Action

DTO

Business Rule

Calculation

Posting Engine

Approval Engine

Semua logika bisnis

berada di layer ini.

---

# INFRASTRUCTURE

Repository

Storage

Mail

Queue

Notification

Cache

External API

Whatsapp

Telegram

PDF

Excel

---

# DATABASE

Migration

Seeder

Factory

Model

Observer

Database Layer

tidak mengetahui

Vue.

---

# REQUEST FLOW

Browser

↓

Vue

↓

Inertia

↓

Controller

↓

FormRequest

↓

Service

↓

Repository

↓

Database

↓

Repository

↓

Service

↓

Controller

↓

Inertia

↓

Vue

---

# ACCOUNTING FLOW

Transaction

↓

Validation

↓

Approval

↓

Posting Engine

↓

Journal Engine

↓

Ledger Engine

↓

Financial Report

---

# INVENTORY FLOW

Purchase

↓

Stock Movement

↓

Inventory Engine

↓

Valuation Engine

↓

Journal

---

# SALES FLOW

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

---

# PURCHASE FLOW

Purchase Order

↓

Goods Receipt

↓

Invoice

↓

Payment

↓

Posting

↓

Journal

---

# POS FLOW

Session

↓

Order

↓

Payment

↓

Receipt

↓

Posting

↓

Journal

---

# AUTHENTICATION

Laravel Auth

↓

Role

↓

Permission

↓

Scope

↓

Company

↓

Branch

↓

Dashboard

---

# DATA SCOPE

Setiap User

WAJIB memiliki

Company

Branch

Role

Permission

Scope

Contoh

Scope

Global

Company

Branch

Own

---

# MULTI COMPANY

Company

↓

Branch

↓

Warehouse

↓

Cash Bank

↓

Transaction

↓

Journal

↓

Report

Semua data

dipisahkan

berdasarkan Company.

---

# MULTI BRANCH

Branch

↓

Warehouse

↓

Cash Bank

↓

User

↓

Transaction

↓

Journal

↓

Report

Semua transaksi

memiliki

branch_id.

---

# SERVICE RULE

Controller

TIDAK BOLEH

Business Logic.

Contoh

SALAH

Controller

↓

Hitung HPP

↓

Posting Journal

↓

Update Stock

BENAR

Controller

↓

InventoryService

↓

PostingService

↓

JournalService

---

# REPOSITORY RULE

Repository

HANYA

Query Database.

Tidak boleh

Business Logic.

---

# EVENT

Purchase Posted

↓

Stock Updated

↓

Journal Created

↓

Notification

↓

Activity Log

Menggunakan

Laravel Event

Listener.

---

# OBSERVER

Model

↓

Created

Updated

Deleted

↓

Activity Log

↓

Code Generator

↓

Notification

---

# QUEUE

Email

PDF

Export

Import

Notification

Whatsapp

Semua proses berat

WAJIB menggunakan Queue.

---

# CACHE

Master Data

Configuration

Permission

Role

Menu

Company

Branch

Menggunakan

Cache.

---

# STORAGE

Public

Private

Temporary

Backup

---

# LOGGING

Application Log

Audit Log

Activity Log

API Log

Error Log

Queue Log

---

# NOTIFICATION

Email

Database

Whatsapp

Telegram

In-App Notification

---

# REPORT ENGINE

Transaction

↓

Journal

↓

Ledger

↓

Report

↓

Dashboard

Semua report

berasal

dari Journal.

---

# DEPENDENCY RULE

Presentation

boleh

↓

Application

Application

boleh

↓

Domain

Domain

boleh

↓

Infrastructure

Infrastructure

boleh

↓

Database

Tidak boleh

sebaliknya.

---

# ERP PRINCIPLE

Single Source of Truth

↓

Journal

↓

Ledger

↓

Financial Report

Tidak ada

saldo manual.

---

# MODULE DEPENDENCY

Foundation

↓

Master Data

↓

Inventory

↓

Purchase

↓

Sales

↓

POS

↓

Accounting

↓

Reporting

↓

Dashboard

---

# VERSION

1.0

Status

Approved

Frozen