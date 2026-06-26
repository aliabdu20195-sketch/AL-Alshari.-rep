# 🏢 Al-Ashari ERP System

نظام ERP محاسبي ذكي مبني على Laravel 11

## ✨ الميزات
- 🛒 نظام مبيعات (POS)
- 📦 إدارة المخزون
- 📊 محاسبة قيد مزدوج
- 🏢 تعدد الشركات
- 🤖 ذكاء اصطناعي
- 🔐 مصادقة آمنة

## 🚀 التثبيت

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## 🔑 بيانات الدخول
Email: admin@erp.com
Password: password

## 📡 API
POST /api/register
POST /api/login
GET  /api/products
POST /api/sales
GET  /api/accounting/accounts
GET  /api/ai/dashboard

## 🛠 التقنيات
- Laravel 11
- MySQL
- Laravel Sanctum
- PHP 8.2
