<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

// شيت واحد داخل ملف الصرف — إما شيت بنك واحد، أو شيت "Held" للموقوفين عن الصرف
class PayrollDisbursementSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Collection $rows,
        private string $sheetTitle,
        private bool $isHeldSheet = false
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->isHeldSheet
            ? ['كود الموظف', 'الاسم', 'البنك', 'رقم الحساب', 'سبب الإيقاف']
            : ['كود الموظف', 'الاسم', 'البنك', 'رقم الحساب', 'صافي الراتب'];
    }

    public function map($p): array
    {
        $emp = $p->employee;

        return $this->isHeldSheet
            ? [$emp->employee_id ?? '—', $emp->employee_name_A ?? '—', $emp->bank_name ?? '—', $emp->bank_account ?? '—', $p->hold_reason ?? '']
            : [$emp->employee_id ?? '—', $emp->employee_name_A ?? '—', $emp->bank_name ?? '—', $emp->bank_account ?? '—', $p->net_salary];
    }

    public function title(): string
    {
        // اسم الشيت فى Excel لا يتجاوز 31 حرفًا ولا يقبل [ ] * / \ ? :
        $safe = preg_replace('/[\[\]\*\/\\\\\?:]/', '', $this->sheetTitle);
        return mb_substr($safe ?: 'Sheet', 0, 31);
    }
}
