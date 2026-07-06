<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير العمولات</title>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      tr { page-break-inside: avoid; }
    }
    @page { size: A4 landscape; margin: 10mm 8mm; }

    body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 12px; direction: rtl; background: #fff; color: #222; }

    .header { text-align: center; margin-bottom: 14px; border-bottom: 3px solid #1a6f3c; padding-bottom: 8px; }
    .header h2 { color: #1a6f3c; margin: 0 0 4px; font-size: 18px; }
    .header .sub { font-size: 11px; color: #555; }

    .filters-bar { background: #f0faf4; border: 1px solid #a3d9b1; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 11px; color: #333; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .filters-bar span { white-space: nowrap; }
    .filters-bar strong { color: #1a6f3c; }

    .stats-row { display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 120px; border-radius: 6px; padding: 8px 12px; text-align: center; font-size: 11px; }
    .stat-card .val { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .card-approved  { background: #d4edda; color: #155724; border: 1px solid #a3d9b1; }
    .card-pending   { background: #fff3cd; color: #856404; border: 1px solid #f6d860; }
    .card-cancelled { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .card-total     { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }

    table { width: 100%; border-collapse: collapse; }
    thead th { background: #1a6f3c; color: #fff; padding: 6px 5px; text-align: center; font-size: 11px; }
    tbody td { padding: 5px; border: 1px solid #ddd; font-size: 11px; text-align: center; }
    tbody tr:nth-child(even) td { background: #f9f9f9; }

    .row-approved  td { background: #eafaf0 !important; }
    .row-pending   td { background: #fffdf0 !important; }
    .row-cancelled td { background: #fff5f5 !important; opacity: .75; }

    .badge-approved  { background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
    .badge-pending   { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
    .badge-cancelled { background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 10px; font-size: 10px; }

    tfoot td { font-weight: bold; background: #e8f5e9; border-top: 2px solid #1a6f3c; }

    .print-btn { display: inline-block; margin: 6px 4px; padding: 7px 20px; background: #1a6f3c; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .close-btn  { background: #555; }
    .td-name    { text-align: right !important; }
    .amount     { font-weight: 600; color: #1a6f3c; }
  </style>
</head>
<body>

  <div class="header">
    <h2>تقرير العمولات</h2>
    <div class="sub">
      عدد السجلات: <strong>{{ $data->count() }}</strong>
      @if(!empty($filters['month']) && !empty($filters['year']))
        &nbsp;|&nbsp; الشهر:
        <strong>{{ ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'][$filters['month']] ?? '' }}
        {{ $filters['year'] }}</strong>
      @elseif(!empty($filters['year']))
        &nbsp;|&nbsp; السنة: <strong>{{ $filters['year'] }}</strong>
      @endif
      &nbsp;|&nbsp; تاريخ التقرير: <strong>{{ now()->format('Y-m-d') }}</strong>
    </div>
  </div>

  {{-- إجماليات --}}
  @php
    $approved  = $data->where('status', 1);
    $pending   = $data->where('status', 2);
    $cancelled = $data->where('status', 3);
  @endphp
  <div class="stats-row no-print">
    <div class="stat-card card-approved">
      <div class="val">{{ number_format($approved->sum('amount'), 2) }} ج.م</div>
      <div>معتمدة ({{ $approved->count() }} سجل)</div>
    </div>
    <div class="stat-card card-pending">
      <div class="val">{{ number_format($pending->sum('amount'), 2) }} ج.م</div>
      <div>معلقة ({{ $pending->count() }} سجل)</div>
    </div>
    <div class="stat-card card-cancelled">
      <div class="val">{{ number_format($cancelled->sum('amount'), 2) }} ج.م</div>
      <div>ملغاة ({{ $cancelled->count() }} سجل)</div>
    </div>
    <div class="stat-card card-total">
      <div class="val">{{ number_format($approved->sum('amount') + $pending->sum('amount'), 2) }} ج.م</div>
      <div>إجمالي (معتمد + معلق)</div>
    </div>
  </div>

  {{-- شريط الفلاتر --}}
  <div class="filters-bar">
    @if(!empty($filters['employee_id']) && isset($employeeName))
      <span>👤 الموظف: <strong>{{ $employeeName }}</strong></span>
    @endif
    @if(!empty($filters['month']))
      <span>📅 الشهر: <strong>{{ ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'][$filters['month']] ?? $filters['month'] }}</strong></span>
    @endif
    @if(!empty($filters['year']))
      <span>📅 السنة: <strong>{{ $filters['year'] }}</strong></span>
    @endif
    @if(!empty($filters['status']))
      <span>الحالة: <strong>{{ ['1'=>'معتمدة','2'=>'معلقة','3'=>'ملغاة'][$filters['status']] ?? '' }}</strong></span>
    @endif
    @if(!empty($filters['commission_type']))
      <span>النوع: <strong>{{ $filters['commission_type'] }}</strong></span>
    @endif
    @php $sortLabels = ['date_desc'=>'التاريخ (الأحدث)','date_asc'=>'التاريخ (الأقدم)','amount_desc'=>'المبلغ (الأعلى)','amount_asc'=>'المبلغ (الأقل)','month_desc'=>'الشهر (الأحدث)','month_asc'=>'الشهر (الأقدم)']; @endphp
    <span>الترتيب: <strong>{{ $sortLabels[$filters['sort_by'] ?? 'date_desc'] ?? '' }}</strong></span>
  </div>

  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn close-btn" onclick="window.close()">✕ إغلاق</button>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:30px">#</th>
        <th>اسم الموظف</th>
        <th>رقم الموظف</th>
        <th>نوع العمولة</th>
        <th>المبلغ (ج.م)</th>
        <th>الشهر / السنة</th>
        <th>تاريخ الإضافة</th>
        <th>الحالة</th>
        <th>ملاحظات</th>
      </tr>
    </thead>
    <tbody>
      @php
        $months = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
      @endphp
      @forelse($data as $i => $com)
      @php
        $rowClass = ['1'=>'row-approved','2'=>'row-pending','3'=>'row-cancelled'][$com->status] ?? '';
        $badgeClass = ['1'=>'badge-approved','2'=>'badge-pending','3'=>'badge-cancelled'][$com->status] ?? '';
        $statusLabel = ['1'=>'معتمدة','2'=>'معلقة','3'=>'ملغاة'][$com->status] ?? '';
      @endphp
      <tr class="{{ $rowClass }}">
        <td>{{ $i + 1 }}</td>
        <td class="td-name">{{ $com->employee->employee_name_A ?? '—' }}</td>
        <td>{{ $com->employee->employee_id ?? '—' }}</td>
        <td>{{ $com->commission_type ?? '—' }}</td>
        <td class="amount">{{ number_format($com->amount, 2) }}</td>
        <td>{{ $months[$com->month] ?? $com->month }} {{ $com->year }}</td>
        <td>{{ $com->commission_date }}</td>
        <td><span class="{{ $badgeClass }}">{{ $statusLabel }}</span></td>
        <td class="td-name" style="font-size:10px">{{ $com->notes ?? '' }}</td>
      </tr>
      @empty
        <tr><td colspan="9" style="text-align:center;padding:20px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
    @if($data->count())
    <tfoot>
      <tr>
        <td colspan="4" style="text-align:right">إجمالي المعتمد</td>
        <td class="amount">{{ number_format($approved->sum('amount'), 2) }}</td>
        <td colspan="4"></td>
      </tr>
      @if($pending->count())
      <tr>
        <td colspan="4" style="text-align:right">إجمالي المعلق</td>
        <td style="color:#856404;font-weight:600">{{ number_format($pending->sum('amount'), 2) }}</td>
        <td colspan="4"></td>
      </tr>
      @endif
      <tr style="background:#c8e6c9">
        <td colspan="4" style="text-align:right">الإجمالي الكلي (معتمد + معلق)</td>
        <td class="amount">{{ number_format($approved->sum('amount') + $pending->sum('amount'), 2) }}</td>
        <td colspan="4"></td>
      </tr>
    </tfoot>
    @endif
  </table>

</body>
</html>
