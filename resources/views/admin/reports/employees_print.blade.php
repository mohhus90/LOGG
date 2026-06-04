<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير الموظفين</title>
  <style>
    @media print { .no-print{display:none!important} body{margin:0} }
    body{font-family:'Segoe UI',Tahoma,sans-serif;font-size:11px;direction:rtl;background:#fff}
    .header{text-align:center;margin-bottom:14px;border-bottom:2px solid #2f855a;padding-bottom:8px}
    .header h2{color:#2f855a;margin:0 0 4px}
    table{width:100%;border-collapse:collapse;font-size:10.5px}
    th{background:#2f855a;color:#fff;padding:5px 3px;text-align:center}
    td{padding:4px 3px;border:1px solid #ddd;text-align:center}
    tr:nth-child(even) td{background:#f0fff4}
    .print-btn{display:inline-block;margin:8px 4px;padding:7px 18px;background:#2f855a;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:13px}
  </style>
</head>
<body>
  <div class="header">
    <h2>تقرير الموظفين</h2>
    <small>إجمالي: {{ $data->count() }} موظف</small>
  </div>
  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn" style="background:#555" onclick="window.close()">✕ إغلاق</button>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>الاسم</th><th>رقم الموظف</th><th>الإدارة</th>
        <th>الوظيفة</th><th>الراتب</th><th>الحالة</th><th>تاريخ التعيين</th><th>الجوال</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $i => $emp)
      <tr>
        <td>{{ $i+1 }}</td>
        <td style="text-align:right">{{ $emp->employee_name_A }}</td>
        <td>{{ $emp->employee_id }}</td>
        <td>{{ $emp->department->dep_name   ?? '—' }}</td>
        <td>{{ $emp->jobs_categories->job_name ?? '—' }}</td>
        <td>{{ number_format($emp->emp_sal,2) }}</td>
        <td>{{ $emp->functional_status==1?'يعمل':'لا يعمل' }}</td>
        <td>{{ $emp->emp_start_date }}</td>
        <td>{{ $emp->emp_mobile }}</td>
      </tr>
      @empty
        <tr><td colspan="9" style="text-align:center;padding:18px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
