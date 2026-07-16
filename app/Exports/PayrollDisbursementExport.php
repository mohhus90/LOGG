<?php

namespace App\Exports;

use App\Models\MonthlyPayroll;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// ملف صرف الرواتب: شيت منفصل لكل بنك (يقابل شيتات Alex Bank / CIB / QNB / ...
// فى ملفات العملاء الأصلية)، بالإضافة إلى شيت "Cash" للي بيُصرف كاش، وشيت
// "Held" للموظفين الموقوفين عن الصرف هذا الشهر (is_held).
class PayrollDisbursementExport implements WithMultipleSheets
{
    protected array $filters;
    protected int $comCode;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->comCode = (int) Auth::guard('admin')->user()->com_code;
    }

    public function sheets(): array
    {
        $query = MonthlyPayroll::with('employee')
            ->where('com_code', $this->comCode)
            ->whereIn('status', [2, 3]); // معتمد أو مدفوع فقط — المسودات لا تُصرف

        if (!empty($this->filters['month'])) $query->where('month', $this->filters['month']);
        if (!empty($this->filters['year']))  $query->where('year', $this->filters['year']);
        if (!empty($this->filters['client_id'])) {
            $query->whereHas('employee', fn($q) => $q->where('client_id', $this->filters['client_id']));
        }

        $payrolls = $query->get();

        $held   = $payrolls->filter(fn($p) => (bool) $p->is_held);
        $active = $payrolls->reject(fn($p) => (bool) $p->is_held);

        $byBank = $active->groupBy(function ($p) {
            $bank = trim((string) ($p->employee->bank_name ?? ''));
            // توحيد "Cash"/"cash" (والقيمة الفارغة) فى شيت نقدي واحد بغض النظر عن حالة الأحرف
            return ($bank === '' || strcasecmp($bank, 'cash') === 0) ? 'Cash' : $bank;
        });

        $sheets = [];
        foreach ($byBank as $bankName => $rows) {
            $sheets[] = new PayrollDisbursementSheet($rows, (string) $bankName);
        }
        if ($held->isNotEmpty()) {
            $sheets[] = new PayrollDisbursementSheet($held, 'Held', true);
        }
        if (empty($sheets)) {
            $sheets[] = new PayrollDisbursementSheet($payrolls, 'لا توجد بيانات');
        }

        return $sheets;
    }
}
