<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name'     => 'شركة الأشعري للتجارة',
            'email'    => 'info@alashari.com',
            'phone'    => '0501234567',
            'address'  => 'الرياض، المملكة العربية السعودية',
            'currency' => 'SAR',
        ]);

        User::create([
            'company_id' => $company->id,
            'name'       => 'محمد الأشعري',
            'email'      => 'admin@erp.com',
            'password'   => bcrypt('password'),
            'role'       => 'admin',
        ]);

        $accounts = [
            ['code' => '1010', 'name' => 'الصندوق - نقدية',      'type' => 'asset',    'normal_balance' => 'debit'],
            ['code' => '1020', 'name' => 'البنك',                 'type' => 'asset',    'normal_balance' => 'debit'],
            ['code' => '1100', 'name' => 'ذمم مدينة',             'type' => 'asset',    'normal_balance' => 'debit'],
            ['code' => '1300', 'name' => 'المخزون',               'type' => 'asset',    'normal_balance' => 'debit'],
            ['code' => '2000', 'name' => 'ذمم دائنة',             'type' => 'liability','normal_balance' => 'credit'],
            ['code' => '2100', 'name' => 'قروض',                  'type' => 'liability','normal_balance' => 'credit'],
            ['code' => '3000', 'name' => 'رأس المال',             'type' => 'equity',   'normal_balance' => 'credit'],
            ['code' => '3100', 'name' => 'الأرباح المحتجزة',      'type' => 'equity',   'normal_balance' => 'credit'],
            ['code' => '4000', 'name' => 'إيراد المبيعات',        'type' => 'revenue',  'normal_balance' => 'credit'],
            ['code' => '4100', 'name' => 'إيرادات أخرى',          'type' => 'revenue',  'normal_balance' => 'credit'],
            ['code' => '5000', 'name' => 'تكلفة البضاعة المباعة', 'type' => 'expense',  'normal_balance' => 'debit'],
            ['code' => '5100', 'name' => 'مصاريف إدارية',         'type' => 'expense',  'normal_balance' => 'debit'],
            ['code' => '5200', 'name' => 'مصاريف تسويق',          'type' => 'expense',  'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $account) {
            Account::create(array_merge($account, ['company_id' => $company->id]));
        }

        $products = [
            ['barcode' => '6001', 'name' => 'سكر أبيض 1كغ',     'cost' => 3.50,  'price' => 5.00,  'stock' => 200, 'min_stock' => 50],
            ['barcode' => '6002', 'name' => 'أرز بسمتي 2كغ',    'cost' => 12.00, 'price' => 17.50, 'stock' => 150, 'min_stock' => 30],
            ['barcode' => '6003', 'name' => 'زيت نباتي 1.5لتر',  'cost' => 8.00,  'price' => 12.00, 'stock' => 80,  'min_stock' => 20],
            ['barcode' => '6004', 'name' => 'شاي أسود 100كيس',   'cost' => 6.00,  'price' => 9.50,  'stock' => 5,   'min_stock' => 20],
            ['barcode' => '6005', 'name' => 'قهوة مطحونة 500غ',  'cost' => 18.00, 'price' => 27.00, 'stock' => 0,   'min_stock' => 10],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, [
                'company_id' => $company->id,
                'unit'       => 'قطعة',
            ]));
        }
    }
}
