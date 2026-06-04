<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><title>تقرير أرصدة الإجازات</title>
  <style>
    @media print{.no-print{display:none!important}body{margin:0}}
    body{font-family:'Segoe UI',Tahoma,sans-serif;font-size:12px;direction:rtl;background:#fff}
    .header{text-align:center;margin-bottom:14px;border-bottom:2px solid #2b6cb0;padding-bottom:8px}
    .header h2{color:#2b6cb0;margin:0 0 4px}
    table{width:100%;border-collapse:collapse}
    th{background:#2b6cb0;color:#fff;padding:6px;text-align:center}
    td{padding:5px;border:1px solid #ddd;text-align:center}
    tr:nth-child(even) td{background:#ebf4ff}
    .print-btn{display:inline-block;margin:8px 4px;padding:7px 18px;background:#2b6cb0;color:#fff;border:none;border-radius:5px;cursor:pointer}
  </style>
</head>
<body>
  <div class="header">
    <h2>تقرير أرصدة الإجازات</h2>
    <small>إجمالي: {{ $data->count() }} موظف</small>
  </div>
  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn" style="background:#555" onclick="window.close()">✕ إغلاق</button>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>اسم الموظف</th><th>رقم الموظف</th>
        <th>رصيد سنوية</th><th>مستنفد سنوية</th><th>متبقي سنوية</th>
        <th>رصيد عارضة</th><th>مستنفد عارضة</th><th>متبقي عارضة</th>
        <th>السنة</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $i => $row)
      <tr>
        <td>{{ $i+1 }}</td>
        <td style="text-align:right">{{ $row->employee->employee_name_A ?? '—' }}</td>
        <td>{{ $row->employee->employee_id ?? '—' }}</td>
        <td>{{ $row->annual_balance   ?? 0 }}</td>
        <td>{{ $row->annual_used      ?? 0 }}</td>
        <td style="font-weight:bold;color:{{ ($row->annual_remaining??0)>0?'#2f855a':'#c53030' }}">
          {{ $row->annual_remaining ?? 0 }}
        </td>
        <td>{{ $row->casual_balance   ?? 0 }}</td>
        <td>{{ $row->casual_used      ?? 0 }}</td>
        <td style="font-weight:bold;color:{{ ($row->casual_remaining??0)>0?'#2f855a':'#c53030' }}">
          {{ $row->casual_remaining ?? 0 }}
        </td>
        <td>{{ $row->year ?? date('Y') }}</td>
      </tr>
      @empty
        <tr><td colspan="10" style="text-align:center;padding:18px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
