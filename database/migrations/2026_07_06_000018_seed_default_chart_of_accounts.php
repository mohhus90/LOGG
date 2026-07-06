<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * يزرع دليل حسابات مصري مبسّط + قواعد الترحيل التلقائي الأساسية
 * لكل شركة (com_code) موجودة بالفعل في جدول admins.
 * Idempotent: يعتمد على updateOrInsert بمفتاح (com_code, account_code) / (com_code, event_type, line_role).
 */
return new class extends Migration
{
    /** account_code => [name, name_en, type, nature, parent_code|null, is_group, allow_cost_center] */
    private function accountTree(): array
    {
        return [
            '1000' => ['الأصول', 'Assets', 'asset', 'debit', null, true, false],
            '1100' => ['الأصول المتداولة', 'Current Assets', 'asset', 'debit', '1000', true, false],
            '1101' => ['الصندوق', 'Cash', 'asset', 'debit', '1100', false, false],
            '1102' => ['البنك', 'Bank', 'asset', 'debit', '1100', false, false],
            '1110' => ['عملاء (مدينون)', 'Accounts Receivable', 'asset', 'debit', '1100', false, false],
            '1120' => ['شيكات تحت التحصيل', 'Cheques Under Collection', 'asset', 'debit', '1100', false, false],
            '1130' => ['المخزون', 'Inventory', 'asset', 'debit', '1100', false, false],
            '1135' => ['تحت التشغيل (إنتاج)', 'Work In Progress', 'asset', 'debit', '1100', false, false],
            '1140' => ['ضريبة القيمة المضافة - مشتريات', 'VAT Input', 'asset', 'debit', '1100', false, false],
            '1200' => ['الأصول الثابتة', 'Fixed Assets', 'asset', 'debit', '1000', true, false],
            '1210' => ['الأصول الثابتة - التكلفة', 'Fixed Assets - Cost', 'asset', 'debit', '1200', false, false],
            '1220' => ['مجمع إهلاك الأصول الثابتة', 'Accumulated Depreciation', 'asset', 'credit', '1200', false, false],

            '2000' => ['الالتزامات', 'Liabilities', 'liability', 'credit', null, true, false],
            '2100' => ['موردون (دائنون)', 'Accounts Payable', 'liability', 'credit', '2000', false, false],
            '2110' => ['ضريبة القيمة المضافة - مبيعات', 'VAT Output', 'liability', 'credit', '2000', false, false],
            '2120' => ['رواتب مستحقة', 'Salaries Payable', 'liability', 'credit', '2000', false, false],
            '2130' => ['شيكات دفع مستحقة', 'Cheques Payable', 'liability', 'credit', '2000', false, false],

            '3000' => ['حقوق الملكية', 'Equity', 'equity', 'credit', null, true, false],
            '3100' => ['رأس المال', 'Capital', 'equity', 'credit', '3000', false, false],
            '3200' => ['الأرباح المرحلة', 'Retained Earnings', 'equity', 'credit', '3000', false, false],

            '4000' => ['الإيرادات', 'Revenue', 'revenue', 'credit', null, true, false],
            '4100' => ['إيرادات المبيعات', 'Sales Revenue', 'revenue', 'credit', '4000', false, true],

            '5000' => ['المصروفات', 'Expenses', 'expense', 'debit', null, true, false],
            '5100' => ['تكلفة البضاعة المباعة', 'Cost of Goods Sold', 'expense', 'debit', '5000', false, true],
            '5200' => ['مصروفات الرواتب والأجور', 'Salaries Expense', 'expense', 'debit', '5000', false, true],
            '5300' => ['مصروف الإهلاك', 'Depreciation Expense', 'expense', 'debit', '5000', false, true],
            '5900' => ['مصروفات عمومية وإدارية', 'General & Admin Expenses', 'expense', 'debit', '5000', false, true],
        ];
    }

    /** event_type => [line_role => [account_code, side]] */
    private function postingRules(): array
    {
        return [
            'sales_invoice_issued' => [
                'AR_CONTROL'    => ['1110', 'debit'],
                'SALES_REVENUE' => ['4100', 'credit'],
                'VAT_OUTPUT'    => ['2110', 'credit'],
            ],
            'sales_invoice_cogs' => [
                'COGS'      => ['5100', 'debit'],
                'INVENTORY' => ['1130', 'credit'],
            ],
            'purchase_invoice_received' => [
                'INVENTORY'  => ['1130', 'debit'],
                'EXPENSE'    => ['5900', 'debit'],
                'VAT_INPUT'  => ['1140', 'debit'],
                'AP_CONTROL' => ['2100', 'credit'],
            ],
            'payroll_approved' => [
                'SALARY_EXPENSE' => ['5200', 'debit'],
                'SALARY_PAYABLE' => ['2120', 'credit'],
            ],
        ];
    }

    public function up(): void
    {
        $now = now();

        $comCodes = DB::table('admins')
            ->selectRaw('DISTINCT COALESCE(NULLIF(com_code, 0), company_id) as cc')
            ->whereRaw('COALESCE(NULLIF(com_code, 0), company_id) IS NOT NULL')
            ->pluck('cc');

        foreach ($comCodes as $comCode) {
            $comCode = (int) $comCode;
            if ($comCode <= 0) continue;

            // 1) دليل الحسابات
            $codeToId    = [];
            $codeToLevel = [];
            foreach ($this->accountTree() as $code => [$name, $nameEn, $type, $nature, $parentCode, $isGroup, $allowCC]) {
                $parentId = $parentCode ? ($codeToId[$parentCode] ?? null) : null;
                $level    = $parentCode ? ($codeToLevel[$parentCode] ?? 1) + 1 : 1;
                $codeToLevel[$code] = $level;

                $id = DB::table('chart_of_accounts')
                    ->where('com_code', $comCode)->where('account_code', $code)
                    ->value('id');

                if (!$id) {
                    $id = DB::table('chart_of_accounts')->insertGetId([
                        'com_code'         => $comCode,
                        'account_code'     => $code,
                        'account_name'     => $name,
                        'account_name_en'  => $nameEn,
                        'account_type'     => $type,
                        'account_nature'   => $nature,
                        'parent_id'        => $parentId,
                        'level'            => $level,
                        'is_group'         => $isGroup,
                        'is_active'        => true,
                        'allow_cost_center'=> $allowCC,
                        'current_balance'  => 0,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
                $codeToId[$code] = $id;
            }

            // 2) قواعد الترحيل التلقائي الأساسية
            foreach ($this->postingRules() as $eventType => $roles) {
                foreach ($roles as $role => [$accountCode, $side]) {
                    $accountId = $codeToId[$accountCode] ?? null;
                    if (!$accountId) continue;

                    DB::table('gl_posting_rules')->updateOrInsert(
                        ['com_code' => $comCode, 'event_type' => $eventType, 'line_role' => $role],
                        [
                            'account_id' => $accountId,
                            'side'       => $side,
                            'is_active'  => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // بيانات إعداد أساسية — لا تُحذف تلقائيًا عند التراجع
    }
};
