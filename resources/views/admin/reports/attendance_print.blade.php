<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير الحضور والانصراف</title>
  <style>
    @media print { .no-print { display:none!important; } body { margin:0; } }
    body { font-family:'Segoe UI',Tahoma,sans-serif; font-size:12px; direction:rtl; background:#fff; }
    .header { text-align:center; margin-bottom:16px; border-bottom:2px solid #2b6cb0; padding-bottom:10px; }
    .header h2 { color:#2b6cb0; margin:0 0 4px; }
    table { width:100%; border-collapse:collapse; font-size:11px; }
    th { background:#2b6cb0; color:#fff; padding:6px 4px; text-align:center; }
    td { padding:5px 4px; border:1px solid #ddd; text-align:center; }
    tr:nth-child(even) td { background:#f0f4ff; }
    .badge-present  { background:#c6f6d5; color:#22543d; padding:2px 6px; border-radius:4px; }
    .badge-absent   { background:#fed7d7; color:#822727; padding:2px 6px; border-radius:4px; }
    .badge-vacation { background:#fefcbf; color:#744210; padding:2px 6px; border-radius:4px; }
    .print-btn { display:inline-block; margin:10px 5px; padding:8px 20px; background:#2b6cb0; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px; }
  </style>
</head>
<body>
  <div class="header">
    <h2>تقرير الحضور والانصراف</h2>
    <small>
      @if(!empty($filters['from_date'])) من: {{ $filters['from_date'] }} @endif
      @if(!empty($filters['to_date'])) إلى: {{ $filters['to_date'] }} @endif
      — عدد السجلات: {{ $data->count() }}
    </small>
  </div>

  <div class="no-print" style="text-align:center;margin-bottom:12px">
    <button class="print-btn" onclick="window.print()"><i>🖨</i> طباعة / حفظ PDF</button>
    <button class="print-btn" style="background:#555" onclick="window.close()">✕ إغلاق</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th><th>اسم الموظف</th><th>رقم الموظف</th><th>التاريخ</th>
        <th>الحضور</th><th>الانصراف</th><th>الحالة</th>
        <th>تأخير (د)</th><th>أوفرتايم (س)</th><th>ملاحظات</th>
      </tr>
    </thead>
    <tbody>
      @php $statuses = [1=>'حاضر',2=>'غائب',3=>'إجازة',4=>'إجازة رسمية',5=>'مأمورية']; @endphp
      @forelse($data as $i => $row)
      <tr>
        <td>{{ $i+1 }}</td>
        <td style="text-align:right">{{ $row->employee->employee_name_A ?? '—' }}</td>
        <td>{{ $row->employee->employee_id ?? '—' }}</td>
        <td>{{ $row->attendance_date }}</td>
        <td>{{ $row->check_in_time  ?? '—' }}</td>
        <td>{{ $row->check_out_time ?? '—' }}</td>
        <td>
          @php $s = $row->status; @endphp
          <span class="{{ $s==1?'badge-present':($s==2?'badge-absent':'badge-vacation') }}">
            {{ $statuses[$s] ?? '—' }}
          </span>
        </td>
        <td>{{ $row->late_minutes   ?? 0 }}</td>
        <td>{{ $row->overtime_hours ?? 0 }}</td>
        <td>{{ $row->notes ?? '' }}</td>
      </tr>
      @empty
        <tr><td colspan="10" style="text-align:center;padding:20px">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
