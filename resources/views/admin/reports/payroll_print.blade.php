<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير كشف الرواتب</title>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      tr { page-break-inside: avoid; }
      .page-break { page-break-before: always; }
    }
    @page { size: A4 landscape; margin: 8mm 7mm; }

    body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 10.5px; direction: rtl; background: #fff; color: #222; }

    .header { text-align: center; margin-bottom: 12px; border-bottom: 3px solid #1a3c6e; padding-bottom: 8px; }
    .header h2 { color: #1a3c6e; margin: 0 0 4px; font-size: 17px; }
    .header .sub { font-size: 11px; color: #555; }

    .filters-bar { background: #edf2fb; border: 1px solid #b8cdf5; border-radius: 6px; padding: 6px 12px; margin-bottom: 10px; font-size: 10px; color: #333; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .filters-bar strong { color: #1a3c6e; }

    .stats-row { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 120px; border-radius: 6px; padding: 8px 10px; text-align: center; font-size: 10px; }
    .stat-card .val { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
    .card-emp   { background: #e8eaf6; color: #1a237e; border: 1px solid #c5cae9; }
    .card-gross { background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; }
    .card-ded   { background: #fce4ec; color: #880e4f; border: 1px solid #f48fb1; }
    .card-net   { background: #e3f2fd; color: #0d47a1; border: 1px solid #90caf9; }

    table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
    thead th {
      background: #1a3c6e; color: #fff; padding: 5px 3px;
      text-align: center; white-space: nowrap; border: 1px solid #12306e;
    }
    thead .sub-head th { background: #2a5298; font-size: 8.5px; padding: 3px 2px; }

    tbody td { padding: 4px 3px; border: 1px solid #dde; text-align: center; }
    tbody tr:nth-child(even) td { background: #f7f9fc; }

    .row-draft    td { background: #fffdf0 !important; }
    .row-approved td { background: #f0faf4 !important; }
    .row-paid     td { background: #f0f6ff !important; }

    .badge-draft    { background: #fff3cd; color: #856404; padding: 1px 6px; border-radius: 8px; font-size: 9px; }
    .badge-approved { background: #d4edda; color: #155724; padding: 1px 6px; border-radius: 8px; font-size: 9px; }
    .badge-paid     { background: #cce5ff; color: #004085; padding: 1px 6px; border-radius: 8px; font-size: 9px; }

    tfoot td { font-weight: bold; background: #dde8f8; border-top: 2px solid #1a3c6e; font-size: 10px; }
    tfoot td.net-total { color: #0d47a1; font-size: 12px; }

    .td-name { text-align: right !important; padding-right: 5px !important; }
    .td-num  { font-weight: 600; }
    .text-success { color: #155724; }
    .text-danger  { color: #721c24; }
    .text-primary { color: #0d47a1; }

    .section-label { background: #e8edf8; color: #1a3c6e; font-size: 9px; font-weight: bold; text-align: center; padding: 2px; }

    .print-btn { display: inline-block; margin: 6px 4px; padding: 7px 20px; background: #1a3c6e; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .close-btn  { background: #555; }

    .legend { display: flex; gap: 10px; margin-bottom: 8px; font-size: 9px; align-items: center; }
    .legend-item { display: flex; align-items: center; gap: 4px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 2px; }
  </style>
</head>
<body>

  <div class="header">
    <h2>كشف الرواتب الشهري</h2>
    <div class="sub">
      @php
        $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
      @endphp
      @if(!empty($filters['month']) && !empty($filters['year']))
        الشهر: <strong>{{ $monthNames[$filters['month']] ?? $filters['month'] }} {{ $filters['year'] }}</strong> &nbsp;|&nbsp;
      @elseif(!empty($filters['year']))
        السنة: <strong>{{ $filters['year'] }}</strong> &nbsp;|&nbsp;
      @endif
      عدد الموظفين: <strong>{{ $data->count() }}</strong>
      &nbsp;|&nbsp; تاريخ التقرير: <strong>{{ now()->format('Y-m-d') }}</strong>
    </div>
  </div>

  {{-- شريط الفلاتر --}}
  <div class="filters-bar">
    @if(!empty($employeeName))
      <span>👤 الموظف: <strong>{{ $employeeName }}</strong></span>
    @endif
    @if(!empty($filters['month']))
      <span>📅 الشهر: <strong>{{ $monthNames[$filters['month']] ?? $filters['month'] }}</strong></span>
    @endif
    @if(!empty($filters['year']))
      <span>📅 السنة: <strong>{{ $filters['year'] }}</strong></span>
    @endif
    @if(!empty($filters['status']))
      <span>الحالة: <strong>{{ ['1'=>'مسودة','2'=>'معتمد','3'=>'مدفوع'][$filters['status']] ?? '' }}</strong></span>
    @endif
    @if(!empty($branchName))
      <span>🏢 الفرع: <strong>{{ $branchName }}</strong></span>
    @endif
    @php
      $sortLabels = [
        'name_asc'   => 'الاسم أ-ي',  'name_desc'  => 'الاسم ي-أ',
        'net_desc'   => 'الصافي (الأعلى)', 'net_asc' => 'الصافي (الأقل)',
        'gross_desc' => 'الإجمالي (الأعلى)', 'gross_asc' => 'الإجمالي (الأقل)',
        'month_desc' => 'الشهر (الأحدث)', 'month_asc' => 'الشهر (الأقدم)',
      ];
    @endphp
    <span>الترتيب: <strong>{{ $sortLabels[$filters['sort_by'] ?? 'name_asc'] ?? '' }}</strong></span>
  </div>

  {{-- بطاقات الإجماليات --}}
  @php
    $totalGross = $data->sum('gross_salary');
    $totalNet   = $data->sum('net_salary');
    $totalDed   = $data->sum(fn($p) =>
      $p->late_deductions + $p->absence_deductions + $p->deductions_amount
      + $p->advance_installment + $p->insurance_deduction
      + ($p->kpi_deduction_amount ?? 0) + ($p->sanctions_deduction ?? 0)
    );
  @endphp
  <div class="stats-row no-print">
    <div class="stat-card card-emp">
      <div class="val">{{ $data->count() }}</div>
      <div>موظف</div>
    </div>
    <div class="stat-card card-gross">
      <div class="val">{{ number_format($totalGross, 2) }}</div>
      <div>إجمالي الرواتب (ج.م)</div>
    </div>
    <div class="stat-card card-ded">
      <div class="val">{{ number_format($totalDed, 2) }}</div>
      <div>إجمالي الخصومات (ج.م)</div>
    </div>
    <div class="stat-card card-net">
      <div class="val">{{ number_format($totalNet, 2) }}</div>
      <div>إجمالي الصافي (ج.م)</div>
    </div>
  </div>

  {{-- مفتاح الألوان --}}
  <div class="legend no-print">
    <span>مفتاح الألوان:</span>
    <div class="legend-item"><div class="legend-dot" style="background:#fffdf0;border:1px solid #ddd"></div> مسودة</div>
    <div class="legend-item"><div class="legend-dot" style="background:#f0faf4;border:1px solid #ddd"></div> معتمد</div>
    <div class="legend-item"><div class="legend-dot" style="background:#f0f6ff;border:1px solid #ddd"></div> مدفوع</div>
  </div>

  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn close-btn" onclick="window.close()">✕ إغلاق</button>
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2" style="width:25px">#</th>
        <th rowspan="2">الموظف</th>
        <th rowspan="2">رقمه</th>
        <th rowspan="2">الفرع</th>
        <th rowspan="2">الشهر/السنة</th>
        <th colspan="4" class="section-label" style="color:#fff;background:#2a5298">الحضور</th>
        <th colspan="8" class="section-label" style="color:#fff;background:#1b5e20">المستحقات</th>
        <th colspan="7" class="section-label" style="color:#fff;background:#880e4f">الخصومات</th>
        <th rowspan="2" style="background:#0d47a1">الصافي (ج.م)</th>
        <th rowspan="2">الحالة</th>
      </tr>
      <tr class="sub-head">
        <th>حضور</th><th>غياب</th><th>إجازة</th><th>إجازة أسبوعية</th>
        <th>الأساسي</th><th>المستحق</th><th>بدلات ثابتة</th><th>أوفرتايم</th><th>عمولات</th>
        <th>مكافآت</th><th>بدل إجازة أسبوعية</th><th>مكافأة KPI</th>
        <th>تأخير</th><th>غياب</th><th>أخرى</th><th>سلفة</th><th>تأمين</th><th>خصم KPI</th><th>جزاءات</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $i => $p)
      @php
        $rowClass    = ['1'=>'row-draft','2'=>'row-approved','3'=>'row-paid'][$p->status] ?? '';
        $badgeClass  = ['1'=>'badge-draft','2'=>'badge-approved','3'=>'badge-paid'][$p->status] ?? '';
        $statusLabel = ['1'=>'مسودة','2'=>'معتمد','3'=>'مدفوع'][$p->status] ?? '';
        $totalDedRow = $p->late_deductions + $p->absence_deductions + $p->deductions_amount
                     + $p->advance_installment + $p->insurance_deduction
                     + ($p->kpi_deduction_amount ?? 0) + ($p->sanctions_deduction ?? 0);
        $monthStr = ($monthNames[$p->month] ?? $p->month) . ' ' . $p->year;
      @endphp
      <tr class="{{ $rowClass }}">
        <td>{{ $i + 1 }}</td>
        <td class="td-name">
          <strong>{{ $p->employee->employee_name_A ?? '—' }}</strong>
          @if($p->period_from)
            <br><span style="font-size:8px;color:#888">{{ $p->period_from }} إلى {{ $p->period_to }}</span>
          @endif
        </td>
        <td>{{ $p->employee->employee_id ?? '—' }}</td>
        <td>{{ $p->employee->branches->branch_name ?? '—' }}</td>
        <td>{{ $monthStr }}</td>

        {{-- حضور --}}
        <td class="text-success td-num">{{ $p->work_days }}</td>
        <td class="text-danger td-num">{{ $p->absence_days }}</td>
        <td>{{ $p->leave_days }}</td>
        <td>{{ $p->weekly_off_days ?? 0 }}</td>

        {{-- مستحقات --}}
        <td>{{ number_format($p->basic_salary, 0) }}</td>
        <td class="td-num">{{ number_format($p->earned_salary, 2) }}</td>
        <td class="text-success">{{ $p->fixed_allowances > 0 ? number_format($p->fixed_allowances, 2) : '—' }}</td>
        <td class="text-success">{{ $p->overtime_amount > 0 ? number_format($p->overtime_amount, 2) : '—' }}</td>
        <td class="text-success">{{ $p->commissions_amount > 0 ? number_format($p->commissions_amount, 2) : '—' }}</td>
        <td class="text-success">{{ $p->bonuses_amount > 0 ? number_format($p->bonuses_amount, 2) : '—' }}</td>
        <td class="text-success">{{ ($p->leave_compensation_amount ?? 0) > 0 ? number_format($p->leave_compensation_amount, 2) : '—' }}</td>
        <td class="text-success">{{ ($p->kpi_bonus_amount ?? 0) > 0 ? number_format($p->kpi_bonus_amount, 2) : '—' }}</td>

        {{-- خصومات --}}
        <td class="text-danger">{{ $p->late_deductions > 0 ? number_format($p->late_deductions, 2) : '—' }}</td>
        <td class="text-danger">{{ $p->absence_deductions > 0 ? number_format($p->absence_deductions, 2) : '—' }}</td>
        <td class="text-danger">{{ $p->deductions_amount > 0 ? number_format($p->deductions_amount, 2) : '—' }}</td>
        <td class="text-danger">{{ $p->advance_installment > 0 ? number_format($p->advance_installment, 2) : '—' }}</td>
        <td class="text-danger">{{ $p->insurance_deduction > 0 ? number_format($p->insurance_deduction, 2) : '—' }}</td>
        <td class="text-danger">{{ ($p->kpi_deduction_amount ?? 0) > 0 ? number_format($p->kpi_deduction_amount, 2) : '—' }}</td>
        <td class="text-danger">{{ ($p->sanctions_deduction ?? 0) > 0 ? number_format($p->sanctions_deduction, 2) : '—' }}</td>

        {{-- الصافي --}}
        <td class="text-primary td-num" style="font-size:11px;font-weight:700">
          {{ number_format($p->net_salary, 2) }}
        </td>
        <td><span class="{{ $badgeClass }}">{{ $statusLabel }}</span></td>
      </tr>
      @empty
        <tr><td colspan="25" style="text-align:center;padding:20px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
    @if($data->count())
    <tfoot>
      <tr>
        <td colspan="5" style="text-align:right">الإجمالي</td>
        <td class="text-success">{{ $data->sum('work_days') }}</td>
        <td class="text-danger">{{ $data->sum('absence_days') }}</td>
        <td>{{ $data->sum('leave_days') }}</td>
        <td>{{ $data->sum('weekly_off_days') }}</td>
        <td>{{ number_format($data->sum('basic_salary'), 2) }}</td>
        <td>{{ number_format($data->sum('earned_salary'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('fixed_allowances'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('overtime_amount'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('commissions_amount'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('bonuses_amount'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('leave_compensation_amount'), 2) }}</td>
        <td class="text-success">{{ number_format($data->sum('kpi_bonus_amount'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('late_deductions'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('absence_deductions'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('deductions_amount'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('advance_installment'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('insurance_deduction'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('kpi_deduction_amount'), 2) }}</td>
        <td class="text-danger">{{ number_format($data->sum('sanctions_deduction'), 2) }}</td>
        <td class="net-total">{{ number_format($data->sum('net_salary'), 2) }}</td>
        <td></td>
      </tr>
    </tfoot>
    @endif
  </table>

  {{-- ملخص مطبوع في أسفل الصفحة --}}
  <div style="margin-top:14px;display:flex;gap:20px;font-size:11px;border-top:2px solid #1a3c6e;padding-top:8px">
    <span>إجمالي الرواتب الإجمالية: <strong style="color:#1b5e20">{{ number_format($data->sum('gross_salary'), 2) }} ج.م</strong></span>
    <span>إجمالي الخصومات: <strong style="color:#880e4f">{{ number_format($totalDed, 2) }} ج.م</strong></span>
    <span>إجمالي الرواتب الصافية: <strong style="color:#0d47a1;font-size:13px">{{ number_format($data->sum('net_salary'), 2) }} ج.م</strong></span>
    <span style="margin-right:auto;color:#888">عدد السجلات: {{ $data->count() }}</span>
  </div>

</body>
</html>
