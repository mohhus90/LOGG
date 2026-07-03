<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تقرير مؤشرات الأداء KPI</title>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      tr { page-break-inside: avoid; }
      .employee-block { page-break-inside: avoid; }
    }
    @page { size: A4 landscape; margin: 10mm 8mm; }

    body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px; direction: rtl; background: #fff; color: #222; }

    .header { text-align: center; margin-bottom: 14px; border-bottom: 3px solid #4e1f88; padding-bottom: 8px; }
    .header h2 { color: #4e1f88; margin: 0 0 4px; font-size: 18px; }
    .header .sub { font-size: 11px; color: #555; }

    .filters-bar { background: #f5f0ff; border: 1px solid #c8b4f5; border-radius: 6px; padding: 6px 12px; margin-bottom: 12px; font-size: 11px; color: #333; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .filters-bar strong { color: #4e1f88; }

    .stats-row { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 120px; border-radius: 6px; padding: 8px 12px; text-align: center; font-size: 11px; }
    .stat-card .val { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .card-score    { background: #e8d5fb; color: #4e1f88; border: 1px solid #c8b4f5; }
    .card-bonus    { background: #d4edda; color: #155724; border: 1px solid #a3d9b1; }
    .card-deduct   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .card-achieve  { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

    .top3 { display: flex; gap: 8px; margin-bottom: 14px; }
    .top3-card { flex: 1; border-radius: 8px; padding: 10px; text-align: center; }
    .top3-1 { background: linear-gradient(135deg,#FFD700,#FFA500); color: #fff; }
    .top3-2 { background: linear-gradient(135deg,#C0C0C0,#A0A0A0); color: #fff; }
    .top3-3 { background: linear-gradient(135deg,#CD7F32,#A0522D); color: #fff; }
    .top3-card .name { font-weight: 700; font-size: 13px; margin: 4px 0; }
    .top3-card .score { font-size: 18px; font-weight: 700; }
    .top3-card .medal { font-size: 20px; }

    .main-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .main-table thead th { background: #4e1f88; color: #fff; padding: 6px 4px; text-align: center; font-size: 10px; }
    .main-table tbody td { padding: 5px 4px; border: 1px solid #ddd; text-align: center; font-size: 10px; }
    .main-table tbody tr:nth-child(even) td { background: #f9f9f9; }

    .pct-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; margin-top: 2px; }
    .pct-fill { height: 100%; border-radius: 3px; }
    .pct-green  { background: #28a745; }
    .pct-blue   { background: #17a2b8; }
    .pct-yellow { background: #ffc107; }
    .pct-red    { background: #dc3545; }

    .detail-section { margin-bottom: 20px; }
    .detail-section h4 { background: #4e1f88; color: #fff; padding: 5px 10px; border-radius: 4px 4px 0 0; margin: 0; font-size: 12px; }
    .detail-table { width: 100%; border-collapse: collapse; }
    .detail-table th { background: #ece4fb; color: #4e1f88; padding: 4px 5px; text-align: center; font-size: 10px; border: 1px solid #c8b4f5; }
    .detail-table td { padding: 4px 5px; border: 1px solid #e0d0f8; text-align: center; font-size: 10px; }
    .detail-table tr:nth-child(even) td { background: #faf7ff; }

    .badge-cat { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 9px; background: #e0d0f8; color: #4e1f88; }
    .text-success { color: #28a745; font-weight: 600; }
    .text-danger  { color: #dc3545; font-weight: 600; }
    .text-primary { color: #4e1f88; font-weight: 600; }

    .print-btn { display: inline-block; margin: 6px 4px; padding: 7px 20px; background: #4e1f88; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .close-btn  { background: #555; }
    .footer-total { background: #e8d5fb; font-weight: bold; }
  </style>
</head>
<body>

  <div class="header">
    <h2>تقرير مؤشرات الأداء KPI</h2>
    <div class="sub">
      @php
        $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $catLabels  = ['performance'=>'أداء','quality'=>'جودة','attendance'=>'حضور','sales'=>'مبيعات','custom'=>'مخصص'];
      @endphp
      الشهر: <strong>{{ $monthNames[$month] ?? $month }} {{ $year }}</strong>
      &nbsp;|&nbsp; عدد الموظفين: <strong>{{ $byEmployee->count() }}</strong>
      &nbsp;|&nbsp; تاريخ التقرير: <strong>{{ now()->format('Y-m-d') }}</strong>
    </div>
  </div>

  {{-- شريط الفلاتر --}}
  <div class="filters-bar">
    @if(!empty($employeeName))
      <span>👤 الموظف: <strong>{{ $employeeName }}</strong></span>
    @endif
    @if(!empty($kpiName))
      <span>📊 المؤشر: <strong>{{ $kpiName }}</strong></span>
    @endif
    @if(!empty($category))
      <span>الفئة: <strong>{{ $catLabels[$category] ?? $category }}</strong></span>
    @endif
  </div>

  {{-- إجماليات --}}
  <div class="stats-row no-print">
    <div class="stat-card card-score">
      <div class="val">{{ round($byEmployee->sum('total_score'), 1) }}</div>
      <div>إجمالي النقاط</div>
    </div>
    <div class="stat-card card-achieve">
      <div class="val">{{ round($byEmployee->avg('avg_achievement'), 1) }}%</div>
      <div>متوسط التحقق</div>
    </div>
    <div class="stat-card card-bonus">
      <div class="val">{{ number_format($byEmployee->sum('total_bonus'), 2) }} ج.م</div>
      <div>إجمالي المكافآت</div>
    </div>
    <div class="stat-card card-deduct">
      <div class="val">{{ number_format($byEmployee->sum('total_deduction'), 2) }} ج.م</div>
      <div>إجمالي الخصومات</div>
    </div>
  </div>

  {{-- أفضل 3 موظفين --}}
  @if($byEmployee->count() >= 2)
  <div class="top3 no-print">
    @foreach($byEmployee->take(3) as $d)
    @php $medals = ['🥇','🥈','🥉']; $cls = ['top3-1','top3-2','top3-3']; @endphp
    <div class="top3-card {{ $cls[$loop->index] ?? '' }}">
      <div class="medal">{{ $medals[$loop->index] ?? '' }}</div>
      <div class="name">{{ $d['employee']->employee_name_A ?? '' }}</div>
      <div class="score">{{ $d['total_score'] }} نقطة</div>
      <div style="font-size:10px">تحقق: {{ $d['avg_achievement'] }}%</div>
      @if($d['net_effect'] > 0)
        <div style="font-size:10px">مكافأة +{{ number_format($d['net_effect'],2) }} ج.م</div>
      @elseif($d['net_effect'] < 0)
        <div style="font-size:10px">خصم {{ number_format(abs($d['net_effect']),2) }} ج.م</div>
      @endif
    </div>
    @endforeach
  </div>
  @endif

  <div class="no-print" style="text-align:center;margin-bottom:10px">
    <button class="print-btn" onclick="window.print()">🖨 طباعة / حفظ PDF</button>
    <button class="print-btn close-btn" onclick="window.close()">✕ إغلاق</button>
  </div>

  {{-- ملخص الموظفين --}}
  <table class="main-table">
    <thead>
      <tr>
        <th>#</th>
        <th>الموظف</th>
        <th>متوسط التحقق %</th>
        <th>مجموع النقاط</th>
        <th>إجمالي المكافآت</th>
        <th>إجمالي الخصومات</th>
        <th>صافي التأثير</th>
        <th>عدد المؤشرات</th>
      </tr>
    </thead>
    <tbody>
      @foreach($byEmployee as $empId => $d)
      @php $pct = $d['avg_achievement']; $pctClass = $pct>=100?'pct-green':($pct>=80?'pct-blue':($pct>=60?'pct-yellow':'pct-red')); @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td style="text-align:right">
          <strong>{{ $d['employee']->employee_name_A ?? '—' }}</strong>
          <br><span style="color:#888;font-size:9px">{{ $d['employee']->employee_id ?? '' }}</span>
        </td>
        <td>
          <div>{{ $pct }}%</div>
          <div class="pct-bar"><div class="pct-fill {{ $pctClass }}" style="width:{{ min($pct,100) }}%"></div></div>
        </td>
        <td class="text-primary">{{ $d['total_score'] }}</td>
        <td class="text-success">{{ number_format($d['total_bonus'],2) }}</td>
        <td class="text-danger">{{ number_format($d['total_deduction'],2) }}</td>
        <td class="{{ $d['net_effect']>=0?'text-success':'text-danger' }}">
          {{ $d['net_effect']>=0?'+':'' }}{{ number_format($d['net_effect'],2) }}
        </td>
        <td>{{ $d['scores']->count() }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="footer-total">
        <td colspan="2" style="text-align:right">الإجمالي</td>
        <td>{{ round($byEmployee->avg('avg_achievement'),1) }}%</td>
        <td class="text-primary">{{ round($byEmployee->sum('total_score'),1) }}</td>
        <td class="text-success">{{ number_format($byEmployee->sum('total_bonus'),2) }}</td>
        <td class="text-danger">{{ number_format($byEmployee->sum('total_deduction'),2) }}</td>
        <td class="{{ $byEmployee->sum('net_effect')>=0?'text-success':'text-danger' }}">
          {{ $byEmployee->sum('net_effect')>=0?'+':'' }}{{ number_format($byEmployee->sum('net_effect'),2) }}
        </td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  {{-- تفاصيل كل موظف --}}
  @foreach($byEmployee as $empId => $d)
  <div class="detail-section employee-block">
    <h4>
      {{ $loop->iteration }}. {{ $d['employee']->employee_name_A ?? '—' }}
      &nbsp;|&nbsp; النقاط: {{ $d['total_score'] }}
      &nbsp;|&nbsp; متوسط التحقق: {{ $d['avg_achievement'] }}%
    </h4>
    <table class="detail-table">
      <thead>
        <tr>
          <th>المؤشر</th>
          <th>الفئة</th>
          <th>الهدف</th>
          <th>الفعلي</th>
          <th>التحقق %</th>
          <th>الوزن</th>
          <th>النقاط</th>
          <th>التأثير المالي</th>
          <th>الاتجاه</th>
        </tr>
      </thead>
      <tbody>
        @foreach($d['scores'] as $sc)
        @php $p = $sc->achievement_pct; @endphp
        <tr>
          <td style="text-align:right"><strong>{{ $sc->kpi->name ?? '—' }}</strong></td>
          <td><span class="badge-cat">{{ $catLabels[$sc->kpi->category ?? ''] ?? '' }}</span></td>
          <td>{{ $sc->kpi->target_value ?? '—' }} {{ $sc->kpi->measurement_unit ?? '' }}</td>
          <td>{{ $sc->actual_value }} {{ $sc->kpi->measurement_unit ?? '' }}</td>
          <td class="{{ $p>=100?'text-success':($p>=60?'':'text-danger') }}">{{ $p }}%</td>
          <td>{{ $sc->kpi->weight ?? '—' }}</td>
          <td class="text-primary">{{ $sc->score }}</td>
          <td>
            @if($sc->salary_effect_amount > 0)
              <span class="{{ $sc->effect_direction==1?'text-success':'text-danger' }}">
                {{ $sc->effect_direction==1?'+':'-' }}{{ number_format($sc->salary_effect_amount,2) }} ج.م
              </span>
            @else —
            @endif
          </td>
          <td>{{ $sc->effect_direction==1?'مكافأة':($sc->effect_direction==2?'خصم':'—') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endforeach

</body>
</html>
