<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\GlPostingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlPostingRulesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    /** الأحداث والأدوار التي يفهمها النظام حاليًا (Phase 1-3) */
    private function knownEvents(): array
    {
        return [
            'sales_invoice_issued'      => ['AR_CONTROL' => 'مدين العميل', 'SALES_REVENUE' => 'إيراد المبيعات', 'VAT_OUTPUT' => 'ضريبة مبيعات'],
            'sales_invoice_cogs'        => ['COGS' => 'تكلفة البضاعة المباعة', 'INVENTORY' => 'المخزون'],
            'purchase_invoice_received' => ['INVENTORY' => 'المخزون', 'EXPENSE' => 'مصروف عام', 'VAT_INPUT' => 'ضريبة مشتريات', 'AP_CONTROL' => 'دائن المورد'],
            'payroll_approved'          => ['SALARY_EXPENSE' => 'مصروف الرواتب', 'SALARY_PAYABLE' => 'رواتب مستحقة'],
            'sales_return_posted'       => ['SALES_REVENUE' => 'عكس إيراد المبيعات', 'VAT_OUTPUT' => 'عكس ضريبة مبيعات', 'AR_CONTROL' => 'دائن العميل'],
            'sales_return_cogs'         => ['INVENTORY' => 'المخزون (إرجاع)', 'COGS' => 'عكس تكلفة البضاعة المباعة'],
            'purchase_return_posted'    => ['AP_CONTROL' => 'مدين المورد', 'INVENTORY' => 'المخزون (إخراج)', 'VAT_INPUT' => 'عكس ضريبة مشتريات'],
        ];
    }

    public function index()
    {
        $comCode = $this->comCode();
        $rules   = GlPostingRule::where('com_code', $comCode)->get()
            ->groupBy('event_type')
            ->map(fn ($rows) => $rows->keyBy('line_role'));
        $accounts = ChartOfAccount::where('com_code', $comCode)->where('is_group', false)->orderBy('account_code')->get();
        $events   = $this->knownEvents();

        return view('admin.accounting.posting_rules.index', compact('rules', 'accounts', 'events'));
    }

    public function update(Request $request)
    {
        $comCode = $this->comCode();
        $mapping = $request->input('mapping', []); // [event_type => [role => account_id]]

        foreach ($mapping as $eventType => $roles) {
            foreach ($roles as $role => $accountId) {
                if (!$accountId) continue;
                $existingSide = GlPostingRule::where('com_code', $comCode)
                    ->where('event_type', $eventType)->where('line_role', $role)
                    ->value('side');
                GlPostingRule::updateOrCreate(
                    ['com_code' => $comCode, 'event_type' => $eventType, 'line_role' => $role],
                    ['account_id' => $accountId, 'side' => $existingSide ?? 'debit', 'is_active' => true]
                );
            }
        }

        return back()->with('success', 'تم حفظ إعدادات الترحيل التلقائي');
    }
}
