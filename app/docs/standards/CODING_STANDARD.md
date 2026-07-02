# ==========================================================
# NUVORA ERP
# CODING STANDARD
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar penulisan kode
untuk seluruh project Nuvora ERP.

Seluruh developer WAJIB mengikuti standar ini.

Tidak diperbolehkan menggunakan style yang berbeda
dalam project yang sama.

---

# Technology Stack

Backend

- Laravel 12

Frontend

- Vue 3

Rendering

- Inertia.js

CSS

- TailwindCSS

Database

- MySQL

ORM

- Eloquent ORM

Icons

- Heroicons

Alert

- SweetAlert2

Chart

- ApexCharts

Build Tool

- Vite

---

# Folder Structure

app/

Http/

Models/

Services/

Repositories/

Traits/

Enums/

Policies/

Observers/

Events/

Listeners/

resources/

js/

Components/

Layouts/

Pages/

Composables/

Utils/

docs/

---

# File Naming

Vue Component

PascalCase

Example

FormInput.vue

SearchableSelect.vue

CurrencyInput.vue

PHP Class

PascalCase

Example

CashBankController

CodeGeneratorService

BranchRepository

Javascript Utility

camelCase

Example

formatter.js

currency.js

permission.js

---

# Variable Naming

Boolean

Gunakan prefix

is

has

can

should

Example

isActive

hasPermission

canDelete

shouldRefresh

Collection

Gunakan bentuk jamak

Example

users

companies

branches

products

Single Object

Gunakan bentuk tunggal

Example

user

company

branch

product

---

# Function Naming

Gunakan verb

create()

store()

update()

destroy()

generateCode()

calculateBalance()

formatCurrency()

syncInventory()

Jangan gunakan

doSomething()

process()

handle()

kecuali memang Event Handler.

---

# Vue Standard

Gunakan

<script setup>

Selalu urutan berikut

Imports

Props

Emits

Reactive State

Computed

Watch

Methods

Lifecycle

Contoh

<script setup>

import

...

const props

...

const emit

...

const form

...

const computedValue

...

watch(...)

function submit()

onMounted()

</script>

---

# Vue Template Order

<template>

PageHeader

Form

Card

Table

Dialog

</template>

---

# Tailwind Standard

Jangan hardcode style
yang sudah tersedia di

DesignSystem.js

Gunakan

Radius

InputSize

Card

ButtonPrimary

NormalClass

ErrorClass

---

# Import Order

Vue

Library

Components

Layouts

Utils

Example

import {

    ref,

    computed,

    watch

}

from 'vue'

import {

    useForm

}

from '@inertiajs/vue3'

import

FormInput

from '@/Components/Form/FormInput.vue'

---

# Formatting

Gunakan format berikut

if (

    condition

) {

}

for (

    ...

) {

}

function submit()
{

}

public function store()
{

}

Jangan menggunakan

if(){

}

atau

function test(){

}

---

# Laravel Controller

Urutan Method

index()

create()

store()

show()

edit()

update()

destroy()

restore()

forceDelete()

---

# Validation

Semua validation

Laravel

Backend

+

ValidationError.vue

Frontend

Tidak diperbolehkan membuat error manual.

---

# Database

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

# Soft Delete

Master Data

WAJIB menggunakan

SoftDeletes

Transaction

Tidak menggunakan

SoftDeletes

kecuali ada kebutuhan khusus.

---

# Code Generation

Semua auto number

menggunakan

CodeGeneratorService

Tidak boleh generate
langsung di Controller.

---

# Business Logic

Seluruh business logic

berada pada

Service

Repository

Action

Bukan Controller.

Controller hanya

Request

↓

Validation

↓

Service

↓

Response

---

# Magic Number

Dilarang

if (

status == 1

)

Gunakan

Enum

Example

StatusEnum::ACTIVE

---

# API Response

Gunakan format

{

success,

message,

data

}

---

# Javascript

Gunakan

const

default

Gunakan

let

jika memang berubah.

Dilarang menggunakan

var

---

# Comments

Gunakan

PHPDoc

JSDoc

untuk Service

Repository

Utility

Jangan memberikan
comment yang tidak perlu.

---

# Git Commit

Gunakan Conventional Commit

feat:

fix:

refactor:

docs:

style:

test:

perf:

Example

feat(accounting): add cash bank create

fix(company): validation required

refactor(form): move input component

docs(uikit): add coding standard

---

# Branch

main

production

develop

feature/*

hotfix/*

release/*

---

# Pull Request

Minimal

Description

Screenshot

Testing Result

Checklist

---

# Component Rules

Semua component

WAJIB

v-model

update:modelValue

Validation

Responsive

Reusable

Dark Mode Ready

---

# Security

Seluruh input

WAJIB divalidasi

Backend

Frontend hanya membantu UX.

Jangan pernah mempercayai
input dari browser.

---

# Performance

Gunakan

Lazy Loading

Pagination

Eager Loading

Debounce

Code Splitting

---

# Documentation

Setiap module baru

WAJIB update

MODULE_ROADMAP.md

CHANGELOG.md

Jika ada perubahan database

WAJIB update

DATABASE_STANDARD.md

---

# Status

Approved

Frozen

Tidak boleh diubah

kecuali Major Version.