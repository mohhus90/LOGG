<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><title>تقرير السلف</title>
  <style>
    @media print{.no-print{display:none!important}body{margin:0}}
    body{font-family:'Segoe UI',Tahoma,sans-serif;font-size:12px;direction:rtl;background:#fff}
    .header{text-align:center;margin-bottom:14px;border-bottom:2px solid #e6a817;padding-bottom:8px}
    .header h2{color:#b7791f;margin:0 0 4px}
    .filters-bar{background:#fffbeb;border:1px solid #f6d860;border-radius:6px;padding:6px 12px;margin-bottom:12px;font-size:11px;color:#555}
    .filters-bar span{margin-left:14px}
    table{width:100%;border-collapse:collapse}
    th{background:#b7791f;color:#fff;padding:6px;text-align:center}
    td{padding:5px;border:1px solid #ddd;text-align:center}
    tr:nth-child(even) td{background:#fffaf0}
    .print-btn{display:inline-block;margin:8px 4px;padding:7px 18px;background:#b7791f;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:12px}
    tfoot td{font-weight:bold;background:#fff3e0}
    .badge-1{color:#856404;background:#fff3cd;padding:2px 7px;border-radius:10px}
    .badge-2{color:#155724;background:#d4edda;padding:2px 7px;border-radius:10px}
    .badge-3{color:#721c24;background:#f8d7da;padding:2px 7px;border-radius:10px}
  </style>
</head>
<body>
  <div class="header">
    <h2><i>تقرير السلف</i></h2>
    <small>عدد السجلات: <strong>{{ $data->count() }}</strong>
      &nbsp;|&nbsp; الإجمالي: <strong>{{ number_format($data->sum('amount'),2) }} ج.م</strong>
    </small>
  </div>

  <div class="filters-bar no-print" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">
    @if(!empty($filters['from_date']))<span>📅 من: <strong>{{ $filters['from_date'] }}</strong></span>@endif
    @if(!empty($filters['to_date']))<span>📅 إلى: <strong>{{ $filters['to_date'] }}</strong></span>@endif
    @if(!empty($filters['branch_id']))
      @php $br = $branches->firstWhere('id',$filters['branch_id']) @endphp
      @if($br)<span>🏢 الفرع: <strong>{{ $br->branch_name }}</strong></span>@endif
    @endif
    @if(!empty($filters['status']))
      <span>الحالة: <strong>{{ ['1'=>'جارية','2'=>'مسددة','3'=>'ملغاة'][$filters['status']] ?? '' }}</strong></span>
    @endif
    @php $sortLabels = ['date_desc'=>'التاريخ (الأحدث)','date_asc'=>'التاريخ (الأقدم)','name_asc'=>'الاسم أ-ي','name_desc'=>'الاسم ي-أ'] @endphp
    <span>الترتيب: <strong>{{ $sortLabels[$sortBy] ?? '' }}</strong></span>
  </div>

  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn" style="background:#555" onclick="window.close()">✕ إغلاق</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>اسم الموظف</th>
        <th>رقم الموظف</th>
        <th>الفرع</th>
        <th>المبلغ</th>
        <th>التاريخ</th>
        <th>الأقساط</th>
        <th>القسط الشهري</th>
        <th>المتبقي</th>
        <th>الحالة</th>
        <th>ملاحظات</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $i => $row)
      @php $statusClass = ['1'=>'badge-1','2'=>'badge-2','3'=>'badge-3'][$row->status] ?? '' @endphp
      @php $statusLabel = ['1'=>'جارية','2'=>'مسددة','3'=>'ملغاة'][$row->status] ?? '' @endphp
      <tr>
        <td>{{ $i+1 }}</td>
        <td style="text-align:right">{{ $row->employee->employee_name_A ?? '—' }}</td>
        <td>{{ $row->employee->employee_id ?? '—' }}</td>
        <td>{{ optional($row->employee ? $row->employee->branches : null)->branch_name ?? '—' }}</td>
        <td><strong>{{ number_format($row->amount,2) }}</strong></td>
        <td>{{ $row->advance_date }}</td>
        <td class="text-center">{{ $row->installments }}</td>
        <td>{{ number_format($row->monthly_installment,2) }}</td>
        <td>{{ number_format($row->remaining_amount,2) }}</td>
        <td><span class="{{ $statusClass }}">{{ $statusLabel }}</span></td>
        <td style="text-align:right;font-size:11px">{{ $row->notes ?? '' }}</td>
      </tr>
      @empty
        <tr><td colspan="11" style="text-align:center;padding:18px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
    @if($data->count())
    <tfoot>
      <tr>
        <td colspan="4" style="text-align:right">الإجمالي</td>
        <td>{{ number_format($data->sum('amount'),2) }}</td>
        <td colspan="3"></td>
        <td>{{ number_format($data->sum('remaining_amount'),2) }}</td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
    @endif
  </table>
</body>
</html>
