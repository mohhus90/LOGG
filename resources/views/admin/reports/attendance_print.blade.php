<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير الحضور والانصراف</title>
  <style>
    @page { size: A4 landscape; margin: 10mm 8mm; }
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      table { page-break-inside: auto; }
      tr { page-break-inside: avoid; }
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      font-size: 10px;
      direction: rtl;
      background: #fff;
      color: #222;
    }

    /* ─── رأس التقرير ─── */
    .report-header {
      text-align: center;
      margin-bottom: 10px;
      border-bottom: 3px solid #1a56a0;
      padding-bottom: 8px;
    }
    .report-header h2 {
      color: #1a56a0;
      margin: 0 0 4px;
      font-size: 16px;
    }
    .report-header .meta { font-size: 10px; color: #555; }
    .filters-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-bottom: 8px;
      font-size: 9.5px;
      color: #444;
    }
    .filters-bar span {
      background: #e8f0fe;
      border: 1px solid #c7d7f9;
      border-radius: 4px;
      padding: 2px 7px;
    }

    /* ─── الجدول ─── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9px;
    }
    thead th {
      background: #1a56a0;
      color: #fff;
      padding: 5px 3px;
      text-align: center;
      border: 1px solid #1345a0;
      font-weight: bold;
      white-space: nowrap;
    }
    tbody td {
      padding: 4px 3px;
      border: 1px solid #dce3f0;
      text-align: center;
      vertical-align: middle;
    }
    tbody tr:nth-child(even) td { background: #f5f8ff; }

    /* ─── حالات الحضور ─── */
    .s-present  { background: #d1fae5; color: #065f46; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }
    .s-absent   { background: #fee2e2; color: #7f1d1d; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }
    .s-leave    { background: #fef9c3; color: #713f12; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }
    .s-official { background: #dbeafe; color: #1e3a8a; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }
    .s-mission  { background: #e5e7eb; color: #374151; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }
    .s-weekly   { background: #ede9fe; color: #4c1d95; padding: 1px 5px; border-radius: 3px; font-weight: bold; display: inline-block; }

    .abs-badge  { background: #1f2937; color: #fff; padding: 1px 4px; border-radius: 3px; font-size: 8px; display: inline-block; margin-top: 1px; }

    /* ─── صف الإجماليات ─── */
    tfoot td {
      background: #1a56a0;
      color: #fff;
      font-weight: bold;
      padding: 5px 3px;
      border: 1px solid #1345a0;
      text-align: center;
    }

    /* ─── أزرار الطباعة ─── */
    .action-bar {
      text-align: center;
      margin-bottom: 12px;
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn-print {
      display: inline-block;
      padding: 7px 20px;
      background: #1a56a0;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-family: inherit;
    }
    .btn-close {
      display: inline-block;
      padding: 7px 20px;
      background: #6b7280;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-family: inherit;
    }
    .stats-bar {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 8px;
    }
    .stat-box {
      border: 1px solid #cbd5e1;
      border-radius: 5px;
      padding: 4px 10px;
      text-align: center;
      min-width: 80px;
    }
    .stat-box .val { font-size: 14px; font-weight: bold; color: #1a56a0; }
    .stat-box .lbl { font-size: 9px; color: #666; }
  </style>
</head>
<body>

@php
  $dayNames = [
    'Saturday'  => 'السبت',  'Sunday'    => 'الأحد',
    'Monday'    => 'الاثنين','Tuesday'   => 'الثلاثاء',
    'Wednesday' => 'الأربعاء','Thursday' => 'الخميس',
    'Friday'    => 'الجمعة',
  ];
  $statusNames = [
    1 => 'حضر', 2 => 'غياب', 3 => 'إجازة',
    4 => 'إجازة رسمية', 5 => 'مأمورية', 6 => 'إجازة أسبوعية',
  ];
  $sortLabels = [
    'date_desc' => 'التاريخ (الأحدث أولاً)',
    'date_asc'  => 'التاريخ (الأقدم أولاً)',
    'name_asc'  => 'الاسم (أ → ي)',
    'name_desc' => 'الاسم (ي → أ)',
  ];

  // إحصائيات
  $totalPresent   = $data->where('status', 1)->count();
  $totalAbsent    = $data->where('status', 2)->count();
  $totalLeave     = $data->whereIn('status', [3,4,5])->count();
  $totalWeekly    = $data->where('status', 6)->count();
  $totalLateMin   = $data->sum('late_minutes');
  $totalOT        = $data->sum('overtime_hours');
  $totalLateDed   = $data->sum('late_deduction');
  $totalEarlyDed  = $data->sum('early_departure_deduction');
  $totalOTAmt     = $data->sum('overtime_amount');
  $totalLeaveComp = $data->where('is_weekly_off_worked', 1)->sum('leave_compensation_amount');
@endphp

{{-- أزرار الطباعة --}}
<div class="action-bar no-print">
  <button class="btn-print" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
  <button class="btn-close" onclick="window.close()">✕ إغلاق</button>
</div>

{{-- رأس التقرير --}}
<div class="report-header">
  <h2>📋 تقرير الحضور والانصراف</h2>
  <div class="meta">
    صدر بتاريخ: {{ now()->format('Y-m-d H:i') }}
    &nbsp;|&nbsp; إجمالي السجلات: <strong>{{ $data->count() }}</strong>
    &nbsp;|&nbsp; الترتيب: <strong>{{ $sortLabels[$sortBy] ?? '—' }}</strong>
  </div>
</div>

{{-- شريط الفلاتر --}}
<div class="filters-bar">
  @if(!empty($filters['from_date'])) <span>📅 من: {{ $filters['from_date'] }}</span> @endif
  @if(!empty($filters['to_date']))   <span>📅 إلى: {{ $filters['to_date'] }}</span> @endif
  @if(!empty($filters['status']))
    <span>الحالة: {{ $statusNames[$filters['status']] ?? '—' }}</span>
  @endif
  @if(empty($filters['employee_id']) && empty($filters['from_date']) && empty($filters['to_date']) && empty($filters['status']))
    <span>جميع السجلات</span>
  @endif
</div>

{{-- إحصائيات سريعة --}}
<div class="stats-bar no-print">
  <div class="stat-box"><div class="val">{{ $data->count() }}</div><div class="lbl">إجمالي</div></div>
  <div class="stat-box"><div class="val" style="color:#065f46">{{ $totalPresent }}</div><div class="lbl">حضر</div></div>
  <div class="stat-box"><div class="val" style="color:#7f1d1d">{{ $totalAbsent }}</div><div class="lbl">غياب</div></div>
  <div class="stat-box"><div class="val" style="color:#713f12">{{ $totalLeave }}</div><div class="lbl">إجازات</div></div>
  <div class="stat-box"><div class="val" style="color:#4c1d95">{{ $totalWeekly }}</div><div class="lbl">إج.أسبوعية</div></div>
  <div class="stat-box"><div class="val">{{ $totalLateMin }}</div><div class="lbl">د تأخير</div></div>
  <div class="stat-box"><div class="val">{{ number_format($totalOT,1) }}</div><div class="lbl">س أوفرتايم</div></div>
</div>

{{-- الجدول الرئيسي --}}
<table>
  <thead>
    <tr>
      <th style="width:22px">#</th>
      <th style="width:90px">الموظف</th>
      <th style="width:40px">رقمه</th>
      <th style="width:60px">التاريخ</th>
      <th style="width:38px">اليوم</th>
      <th style="width:70px">الشيفت</th>
      <th style="width:38px">حضور</th>
      <th style="width:38px">انصراف</th>
      <th style="width:65px">الحالة</th>
      <th style="width:38px">تأخير</th>
      <th style="width:38px">مبكر</th>
      <th style="width:32px">OT(س)</th>
      <th style="width:40px">خ.تأخير</th>
      <th style="width:40px">خ.مبكر</th>
      <th style="width:40px">ق.OT</th>
      <th style="width:40px">بدل إج</th>
      <th style="width:65px">ملاحظات</th>
    </tr>
  </thead>
  <tbody>
    @forelse($data as $i => $rec)
    @php
      $dayEn  = $rec->attendance_date->format('l');
      $dayAr  = $dayNames[$dayEn] ?? $dayEn;
      $isFri  = in_array($dayEn, ['Saturday','Sunday','Friday']);
      $shift  = $rec->effective_shift;
      $lateDisp  = match((int)($rec->late_fraction ?? 0)) {
        1=>'¼ يوم', 2=>'½ يوم', 3=>'يوم', default=>($rec->late_minutes??0).'د'
      };
      $earlyDisp = match((int)($rec->early_departure_fraction ?? 0)) {
        1=>'¼ يوم', 2=>'½ يوم', 3=>'يوم', 4=>'1½ يوم', default=>($rec->early_departure_minutes??0).'د'
      };
      $absDays   = $rec->absence_deduction_days ?? ($settings?->sanctions_value_first_abcence ?? null);
      $leaveComp = ($rec->is_weekly_off_worked && ($rec->leave_compensation_amount??0)>0)
                 ? number_format($rec->leave_compensation_amount,2) : '—';
      $rowBg = '';
      if ($rec->status==2)      $rowBg = 'background:#fff5f5';
      elseif ($isFri)           $rowBg = 'background:#fafaf5';
      elseif ($rec->status==6)  $rowBg = 'background:#f5f0ff';
    @endphp
    <tr style="{{ $rowBg }}">
      <td>{{ $i+1 }}</td>
      <td style="text-align:right; font-size:8.5px">{{ $rec->employee->employee_name_A ?? '—' }}</td>
      <td>{{ $rec->employee->employee_id ?? '—' }}</td>
      <td style="white-space:nowrap">{{ $rec->attendance_date->format('Y-m-d') }}</td>
      <td style="{{ $isFri ? 'color:#b91c1c;font-weight:bold' : '' }}">{{ $dayAr }}</td>
      <td style="font-size:8px; text-align:right">
        @if($shift)
          {{ $shift->type }}<br>
          <span style="color:#666">{{ $shift->from_time }}-{{ $shift->to_time }}</span>
        @else —
        @endif
      </td>
      <td>{{ $rec->check_in_time  ?? '—' }}</td>
      <td>{{ $rec->check_out_time ?? '—' }}</td>
      <td>
        @php
          $cls = match($rec->status) {
            1=>'s-present', 2=>'s-absent', 3=>'s-leave',
            4=>'s-official', 5=>'s-mission', 6=>'s-weekly', default=>''
          };
        @endphp
        <span class="{{ $cls }}">{{ $statusNames[$rec->status] ?? '—' }}</span>
        @if($rec->status==2 && $absDays !== null)
          <br><span class="abs-badge">خصم {{ number_format($absDays,0) }} يوم</span>
        @endif
      </td>
      <td style="{{ ($rec->late_minutes??0)>0 ? 'color:#b91c1c;font-weight:bold' : 'color:#666' }}">
        {{ $rec->status==1 ? $lateDisp : '—' }}
      </td>
      <td style="{{ ($rec->early_departure_minutes??0)>0 ? 'color:#d97706;font-weight:bold' : 'color:#666' }}">
        {{ $rec->status==1 ? $earlyDisp : '—' }}
      </td>
      <td style="{{ ($rec->overtime_hours??0)>0 ? 'color:#059669;font-weight:bold' : '' }}">
        {{ $rec->status==1 ? ($rec->overtime_hours??0) : '—' }}
      </td>
      <td style="{{ ($rec->late_deduction??0)>0 ? 'color:#b91c1c' : '' }}">
        {{ $rec->status==1 ? number_format($rec->late_deduction??0,2) : '—' }}
      </td>
      <td style="{{ ($rec->early_departure_deduction??0)>0 ? 'color:#d97706' : '' }}">
        {{ $rec->status==1 ? number_format($rec->early_departure_deduction??0,2) : '—' }}
      </td>
      <td style="{{ ($rec->overtime_amount??0)>0 ? 'color:#059669;font-weight:bold' : '' }}">
        {{ $rec->status==1 ? number_format($rec->overtime_amount??0,2) : '—' }}
      </td>
      <td style="{{ $leaveComp!='—' ? 'color:#059669;font-weight:bold' : '' }}">{{ $leaveComp }}</td>
      <td style="text-align:right; font-size:8px; color:#555">{{ $rec->notes ?? '' }}</td>
    </tr>
    @empty
      <tr><td colspan="17" style="text-align:center; padding:20px; color:#999">لا توجد بيانات</td></tr>
    @endforelse
  </tbody>
  @if($data->count() > 0)
  <tfoot>
    <tr>
      <td colspan="9" style="text-align:right">الإجماليات</td>
      <td>{{ $totalLateMin }} د</td>
      <td>—</td>
      <td>{{ number_format($totalOT,1) }}</td>
      <td>{{ number_format($totalLateDed,2) }}</td>
      <td>{{ number_format($totalEarlyDed,2) }}</td>
      <td>{{ number_format($totalOTAmt,2) }}</td>
      <td>{{ number_format($totalLeaveComp,2) }}</td>
      <td></td>
    </tr>
  </tfoot>
  @endif
</table>

{{-- ملخص في الأسفل (للطباعة فقط) --}}
<div style="margin-top:10px; font-size:9.5px; border-top:1px solid #ddd; padding-top:6px; color:#555">
  <strong>ملخص:</strong>
  حضر: <strong style="color:#065f46">{{ $totalPresent }}</strong> &nbsp;|&nbsp;
  غياب: <strong style="color:#7f1d1d">{{ $totalAbsent }}</strong> &nbsp;|&nbsp;
  إجازات: <strong style="color:#713f12">{{ $totalLeave }}</strong> &nbsp;|&nbsp;
  إج.أسبوعية: <strong style="color:#4c1d95">{{ $totalWeekly }}</strong> &nbsp;|&nbsp;
  إجمالي تأخير: <strong>{{ $totalLateMin }} د</strong> &nbsp;|&nbsp;
  إجمالي OT: <strong>{{ number_format($totalOT,1) }} س</strong> &nbsp;|&nbsp;
  خصم تأخير: <strong>{{ number_format($totalLateDed,2) }}</strong> &nbsp;|&nbsp;
  قيمة OT: <strong>{{ number_format($totalOTAmt,2) }}</strong>
</div>

</body>
</html>
