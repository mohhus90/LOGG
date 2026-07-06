<?php
namespace App\Http\Controllers\Admin\BI;

use App\Http\Controllers\Controller;
use App\Models\{SalesInvoice, PurchaseInvoice, StockBalance, CashBox, BankAccount, MonthlyPayroll, Employee, ChartOfAccount, JournalEntryLine};
use Illuminate\Support\Facades\Auth;

/**
 * لوحة تنفيذية تجمّع مؤشرات من كل الموديولات الموجودة بالفعل - بدون جداول جديدة،
 * استعلامات تجميع فقط (Phase 9.2).
 */
class BiDashboardController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $comCode = $this->comCode();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();
        $yearStart  = now()->startOfYear()->toDateString();

        $sales = [
            'this_month' => SalesInvoice::where('com_code', $comCode)->where('status', 'issued')
                ->whereBetween('date', [$monthStart, $monthEnd])->sum('total'),
            'this_year'  => SalesInvoice::where('com_code', $comCode)->where('status', 'issued')
                ->whereBetween('date', [$yearStart, $monthEnd])->sum('total'),
            'receivable' => SalesInvoice::where('com_code', $comCode)->whereIn('payment_status', ['unpaid', 'partial'])->sum('remaining_amount'),
        ];

        $purchases = [
            'this_month' => PurchaseInvoice::where('com_code', $comCode)->where('status', 'received')
                ->whereBetween('date', [$monthStart, $monthEnd])->sum('total'),
            'payable'    => PurchaseInvoice::where('com_code', $comCode)->whereIn('payment_status', ['unpaid', 'partial'])->sum('remaining_amount'),
        ];

        $inventoryValue = (float) StockBalance::where('com_code', $comCode)->sum('total_value');

        $treasury = [
            'cash' => (float) CashBox::where('com_code', $comCode)->where('is_active', true)->sum('current_balance'),
            'bank' => (float) BankAccount::where('com_code', $comCode)->where('is_active', true)->sum('current_balance'),
        ];

        $payrollThisMonth = (float) MonthlyPayroll::whereIn('employee_id', Employee::where('com_code', $comCode)->pluck('id'))
            ->where('month', now()->month)->where('year', now()->year)->where('status', '!=', 1)->sum('net_salary');

        $employeesCount = Employee::where('com_code', $comCode)->where('functional_status', 1)->count();

        // ربح/خسارة السنة الحالية حتى تاريخه (نفس منطق AccountingReportsController)
        $revenueTotal = 0.0; $expenseTotal = 0.0;
        $revenueAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'revenue')->where('is_group', false)->get();
        $expenseAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'expense')->where('is_group', false)->get();
        foreach ($revenueAccounts as $acc) {
            $debit  = (float) JournalEntryLine::where('account_id', $acc->id)->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$yearStart, $monthEnd]))->sum('debit');
            $credit = (float) JournalEntryLine::where('account_id', $acc->id)->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$yearStart, $monthEnd]))->sum('credit');
            $revenueTotal += $credit - $debit;
        }
        foreach ($expenseAccounts as $acc) {
            $debit  = (float) JournalEntryLine::where('account_id', $acc->id)->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$yearStart, $monthEnd]))->sum('debit');
            $credit = (float) JournalEntryLine::where('account_id', $acc->id)->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$yearStart, $monthEnd]))->sum('credit');
            $expenseTotal += $debit - $credit;
        }
        $netProfit = $revenueTotal - $expenseTotal;

        // اتجاه المبيعات لآخر 6 أشهر (لرسم بياني بسيط)
        $salesTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $salesTrend[] = [
                'label' => $month->format('Y-m'),
                'total' => (float) SalesInvoice::where('com_code', $comCode)->where('status', 'issued')
                    ->whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('total'),
            ];
        }

        return view('admin.bi.index', compact(
            'sales', 'purchases', 'inventoryValue', 'treasury', 'payrollThisMonth',
            'employeesCount', 'revenueTotal', 'expenseTotal', 'netProfit', 'salesTrend'
        ));
    }
}
