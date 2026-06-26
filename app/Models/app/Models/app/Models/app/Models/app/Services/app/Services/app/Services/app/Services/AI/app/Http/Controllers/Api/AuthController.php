<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:8|confirmed',
        ]);

        $company = Company::create(['name' => $data['company_name']]);

        $user = User::create([
            'company_id' => $company->id,
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
            'role'       => 'admin',
        ]);

        $this->createDefaultAccounts($company->id);

        $token = $user->createToken('erp-token')->plainTextToken;

        return response()->json([
            'user'    => $user->load('company'),
            'token'   => $token,
            'message' => 'تم إنشاء الحساب بنجاح',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة',
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'الحساب موقوف'], 403);
        }

        $token = $user->createToken('erp-token')->plainTextToken;

        return response()->json([
            'user'  => $user->load('company'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('company'));
    }

    private function createDefaultAccounts(int $companyId): void
    {
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
            Account::create(array_merge($account, ['company_id' => $companyId]));
        }
    }
}
