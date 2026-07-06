<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountingReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function periodBounds(Request $request): array
    {
        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        return [$from, $to];
    }

    /** إجمالي مدين/دائن لحساب معيّن خلال فترة (يستبعد القيود التي تم عكسها) */
    private function accountTotals(int $accountId, string $from, string $to): array
    {
        $query = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($from, $to) {
                $q->where('status', 'posted')->whereBetween('entry_date', [$from, $to]);
            });
        return [(float) $query->clone()->sum('debit'), (float) $query->clone()->sum('credit')];
    }

    public function index()
    {
        $comCode = $this->comCode();
        $accountsCount = ChartOfAccount::where('com_code', $comCode)->count();
        return view('admin.accounting.reports.index', compact('accountsCount'));
    }

    public function trialBalance(Request $request)
    {
        [$from, $to] = $this->periodBounds($request);
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->orderBy('account_code')->get();

        $rows = [];
        $totalDebit = 0; $totalCredit = 0;
        foreach ($accounts as $account) {
            [$debit, $credit] = $this->accountTotals($account->id, $from, $to);
            if ($debit == 0 && $credit == 0 && $account->is_group) continue;
            $balance = $account->account_nature === 'debit' ? ($debit - $credit) : ($credit - $debit);
            $rows[] = ['account' => $account, 'debit' => $debit, 'credit' => $credit, 'balance' => $balance];
            $totalDebit  += $debit;
            $totalCredit += $credit;
        }

        return view('admin.accounting.reports.trial_balance', compact('rows', 'from', 'to', 'totalDebit', 'totalCredit'));
    }

    public function incomeStatement(Request $request)
    {
        [$from, $to] = $this->periodBounds($request);
        $comCode = $this->comCode();

        $revenueAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'revenue')->where('is_group', false)->orderBy('account_code')->get();
        $expenseAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'expense')->where('is_group', false)->orderBy('account_code')->get();

        $revenueRows = []; $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            [$debit, $credit] = $this->accountTotals($account->id, $from, $to);
            $amount = $credit - $debit;
            $revenueRows[] = ['account' => $account, 'amount' => $amount];
            $totalRevenue += $amount;
        }

        $expenseRows = []; $totalExpense = 0;
        foreach ($expenseAccounts as $account) {
            [$debit, $credit] = $this->accountTotals($account->id, $from, $to);
            $amount = $debit - $credit;
            $expenseRows[] = ['account' => $account, 'amount' => $amount];
            $totalExpense += $amount;
        }

        $netProfit = $totalRevenue - $totalExpense;

        return view('admin.accounting.reports.income_statement', compact('revenueRows', 'expenseRows', 'totalRevenue', 'totalExpense', 'netProfit', 'from', 'to'));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->input('as_of', now()->toDateString());
        $from = '1970-01-01';
        $comCode = $this->comCode();

        $assetAccounts     = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'asset')->where('is_group', false)->orderBy('account_code')->get();
        $liabilityAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'liability')->where('is_group', false)->orderBy('account_code')->get();
        $equityAccounts    = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'equity')->where('is_group', false)->orderBy('account_code')->get();

        $build = function ($accounts) use ($from, $asOf) {
            $rows = []; $total = 0;
            foreach ($accounts as $account) {
                [$debit, $credit] = $this->accountTotals($account->id, $from, $asOf);
                $amount = $account->account_nature === 'debit' ? ($debit - $credit) : ($credit - $debit);
                $rows[] = ['account' => $account, 'amount' => $amount];
                $total += $amount;
            }
            return [$rows, $total];
        };

        [$assetRows, $totalAssets]         = $build($assetAccounts);
        [$liabilityRows, $totalLiabilities] = $build($liabilityAccounts);
        [$equityRows, $totalEquity]         = $build($equityAccounts);

        // ربح/خسارة العام حتى تاريخه كبند ضمن حقوق الملكية (لم يُقفل بعد كقيد رسمي)
        $revenueAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'revenue')->where('is_group', false)->get();
        $expenseAccounts = ChartOfAccount::where('com_code', $comCode)->where('account_type', 'expense')->where('is_group', false)->get();
        $totalRevenue = 0; $totalExpense = 0;
        foreach ($revenueAccounts as $a) { [$d, $c] = $this->accountTotals($a->id, $from, $asOf); $totalRevenue += $c - $d; }
        foreach ($expenseAccounts as $a) { [$d, $c] = $this->accountTotals($a->id, $from, $asOf); $totalExpense += $d - $c; }
        $currentPeriodProfit = $totalRevenue - $totalExpense;
        $totalEquity += $currentPeriodProfit;

        return view('admin.accounting.reports.balance_sheet', compact(
            'assetRows', 'liabilityRows', 'equityRows', 'totalAssets', 'totalLiabilities', 'totalEquity', 'currentPeriodProfit', 'asOf'
        ));
    }

    public function ledgerDetail(Request $request)
    {
        [$from, $to] = $this->periodBounds($request);
        $comCode = $this->comCode();
        $accounts = ChartOfAccount::where('com_code', $comCode)->where('is_group', false)->orderBy('account_code')->get();

        $account = null; $lines = collect(); $runningBalance = 0;
        if ($request->filled('account_id')) {
            $account = ChartOfAccount::where('com_code', $comCode)->findOrFail($request->account_id);
            $lines = JournalEntryLine::with('journalEntry')
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($from, $to) {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$from, $to]);
                })
                ->get()
                ->sortBy(fn ($l) => $l->journalEntry->entry_date)
                ->map(function ($line) use (&$runningBalance, $account) {
                    $delta = $account->account_nature === 'debit' ? ($line->debit - $line->credit) : ($line->credit - $line->debit);
                    $runningBalance += $delta;
                    $line->running_balance = $runningBalance;
                    return $line;
                });
        }

        return view('admin.accounting.reports.ledger_detail', compact('accounts', 'account', 'lines', 'from', 'to'));
    }
}
