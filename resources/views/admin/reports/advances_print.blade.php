<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><title>تقرير السلف</title>
  <style>
    @media print{.no-print{display:none!important}body{margin:0}}
    body{font-family:'Segoe UI',Tahoma,sans-serif;font-size:12px;direction:rtl;background:#fff}
    .header{text-align:center;margin-bottom:14px;border-bottom:2px solid #e6704a;padding-bottom:8px}
    .header h2{color:#c05621;margin:0 0 4px}
    table{width:100%;border-collapse:collapse}
    th{background:#c05621;color:#fff;padding:6px;text-align:center}
    td{padding:5px;border:1px solid #ddd;text-align:center}
    tr:nth-child(even) td{background:#fffaf0}
    .print-btn{display:inline-block;margin:8px 4px;padding:7px 18px;background:#c05621;color:#fff;border:none;border-radius:5px;cursor:pointer}
    tfoot td{font-weight:bold;background:#fff3e0}
  </style>
</head>
<body>
  <div class="header">
    <h2>تقرير السلف</h2>
    <small>
      @if(!empty($filters['from_date'])) من: {{ $filters['from_date'] }} @endif
      @if(!empty($filters['to_date'])) إلى: {{ $filters['to_date'] }} @endif
      — عدد السجلات: {{ $data->count() }}
    </small>
  </div>
  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn" style="background:#555" onclick="window.close()">✕ إغلاق</button>
  </div>
  <table>
    <thead><tr><th>#</th><th>اسم الموظف</th><th>رقم الموظف</th><th>المبلغ</th><th>التاريخ</th><th>ملاحظات</th></tr></thead>
    <tbody>
      @forelse($data as $i => $row)
      <tr>
        <td>{{ $i+1 }}</td>
        <td style="text-align:right">{{ $row->employee->employee_name_A ?? '—' }}</td>
        <td>{{ $row->employee->employee_id ?? '—' }}</td>
        <td>{{ number_format($row->amount,2) }}</td>
        <td>{{ $row->advance_date }}</td>
        <td>{{ $row->notes ?? '' }}</td>
      </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;padding:18px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
    @if($data->count())
    <tfoot><tr><td colspan="3">الإجمالي</td><td>{{ number_format($data->sum('amount'),2) }}</td><td colspan="2"></td></tr></tfoot>
    @endif
  </table>
</body>
</html>
