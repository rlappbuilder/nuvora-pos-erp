# ==========================================================
# NUVORA ERP
# DEPLOYMENT GUIDE
# Version : 1.0
# Status  : Approved
# ==========================================================

## Objective

Dokumen ini menjadi standar deployment
Nuvora ERP.

Semua Environment

Development

Testing

Staging

Production

WAJIB mengikuti panduan ini.

---

# ENVIRONMENT

Development

↓

Testing

↓

Staging

↓

Production

Tidak diperbolehkan

langsung deploy

ke Production.

---

# SERVER REQUIREMENT

Operating System

Ubuntu Server 24.04 LTS

Recommended

CPU

4 Core

Minimum

2 Core

RAM

Minimum

8 GB

Recommended

16 GB

Storage

SSD

Minimum

100 GB

Recommended

500 GB

---

# SOFTWARE

Nginx

PHP 8.4

Composer

NodeJS LTS

MySQL 8+

Redis

Supervisor

Git

Certbot

---

# DIRECTORY

/var/www/

nuvora-erp/

app/

bootstrap/

config/

database/

public/

resources/

routes/

storage/

vendor/

---

# ENV FILE

Gunakan

.env

Development

.env.testing

Staging

.env.staging

Production

.env.production

Jangan pernah

commit

.env

ke Repository.

---

# FILE PERMISSION

storage

bootstrap/cache

WAJIB

Writable

Gunakan

www-data

sebagai owner.

---

# DATABASE

Development

Database sendiri

Testing

Database sendiri

Production

Database sendiri

Tidak boleh

menggunakan database

yang sama.

---

# DATABASE BACKUP

Backup

Daily

Retention

30 Hari

Backup

Database

Storage

Configuration

Gunakan

Automated Backup.

---

# STORAGE

Public

storage/app/public

Private

storage/app/private

Backup

storage/backup

Temporary

storage/temp

---

# CACHE

Gunakan

Redis

Untuk

Cache

Session

Queue

Rate Limit

---

# QUEUE

Queue Driver

Redis

Gunakan

Supervisor

untuk menjaga

Queue Worker

tetap berjalan.

---

# SCHEDULER

Laravel Scheduler

WAJIB aktif.

Cron

* * * * *

php artisan schedule:run

---

# QUEUE WORKER

Gunakan

Supervisor

Command

php artisan queue:work

Auto Restart

Enabled

---

# OPTIMIZATION

Sebelum Production

jalankan

php artisan optimize

php artisan config:cache

php artisan route:cache

php artisan view:cache

---

# FRONTEND BUILD

Production

npm ci

npm run build

Development

npm install

npm run dev

---

# STORAGE LINK

Jalankan

php artisan storage:link

sekali

setelah deployment.

---

# MIGRATION

Deployment

↓

Backup Database

↓

php artisan migrate

↓

Seeder (optional)

↓

Cache

↓

Restart Queue

---

# SSL

Gunakan

HTTPS

Let's Encrypt

atau

Commercial SSL

HTTP

redirect

ke HTTPS.

---

# NGINX

Gunakan

PHP-FPM

Enable

Gzip

Cache Static File

Security Header

---

# SECURITY

APP_DEBUG=false

APP_ENV=production

APP_KEY

WAJIB

Generate

Tidak boleh

mengaktifkan

Debug

di Production.

---

# LOG

Gunakan

Daily Log

Retention

30 Hari

Error

dipantau

secara rutin.

---

# MONITORING

CPU

RAM

Disk

Queue

Scheduler

Database

Response Time

Gunakan

Uptime Monitor

dan

Alert

untuk notifikasi.

---

# EMAIL

SMTP

Production

WAJIB menggunakan

Mail Server

resmi.

Development

boleh menggunakan

Mailpit

atau

Mailhog.

---

# FILE UPLOAD

Maximum Upload

ditentukan

melalui

Configuration.

Semua file

WAJIB divalidasi.

---

# SESSION

Gunakan

Redis

untuk Production.

File Session

hanya untuk Development.

---

# CACHE CLEAR

Jika ada update

jalankan

php artisan optimize:clear

↓

php artisan optimize

---

# RELEASE PROCESS

Developer

↓

Testing

↓

Staging

↓

User Acceptance Test

↓

Production

↓

Monitoring

↓

Verification

---

# ROLLBACK

Jika deployment gagal

Rollback

Code

↓

Rollback

Migration

(jika diperlukan)

↓

Restore Backup

↓

Restart Service

---

# BACKUP POLICY

Database

Daily

Storage

Weekly

Source Code

Git Repository

Configuration

Version Control

---

# DISASTER RECOVERY

Restore Database

↓

Restore Storage

↓

Restore Configuration

↓

Verify Application

↓

Open Service

---

# CI/CD

Future Ready

GitHub Actions

GitLab CI

Azure DevOps

Jenkins

Deployment

harus dapat

diotomatisasi.

---

# VERSIONING

Gunakan

Semantic Versioning

Major

Minor

Patch

Contoh

v1.0.0

v1.1.0

v1.1.1

---

# RELEASE CHECKLIST

✓ Backup

✓ Migration

✓ Queue

✓ Scheduler

✓ Cache

✓ Build Frontend

✓ SSL

✓ Monitoring

✓ Smoke Test

✓ User Verification

---

# ERP PRINCIPLE

Deployment

tidak boleh

mengubah

Business Logic.

Deployment

harus

Repeatable

Predictable

Recoverable

---

# STATUS

Approved

Version

1.0

Frozen