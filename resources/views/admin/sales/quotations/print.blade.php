<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر رقم {{ $quote->quote_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            font-size: 13px;
            color: #222;
            direction: rtl;
            background: #fff;
        }
        .print-page { max-width: 850px; margin: 0 auto; padding: 30px 40px; }
        .company-header { text-align: center; border-bottom: 3px solid #1a56a7; padding-bottom: 20px; margin-bottom: 20px; }
        .company-header h1 { font-size: 24px; color: #1a56a7; margin-bottom: 5px; }
        .company-header p { color: #555; margin: 2px 0; }
        .doc-title { text-align: center; background: #1a56a7; color: #fff; padding: 10px; font-size: 18px; font-weight: bold; margin-bottom: 20px; border-radius: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #ddd; border-radius: 4px; padding: 12px; }
        .info-box h4 { color: #1a56a7; font-size: 13px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info-box table { width: 100%; }
        .info-box table td { padding: 3px 0; }
        .info-box table td:first-child { color: #666; width: 120px; }
        .info-box table td:last-child { font-weight: bold; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-draft    { background: #6c757d; color: #fff; }
        .status-sent     { background: #17a2b8; color: #fff; }
        .status-accepted { background: #28a745; color: #fff; }
        .status-rejected { background: #dc3545; color: #fff; }
        .status-expired  { background: #ffc107; color: #333; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead { background: #1a56a7; color: #fff; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 8px 10px; text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f5f7fa; }
        table.items tfoot td { font-weight: bold; background: #eef2f7; }
        .totals-section { display: flex; justify-content: flex-start; }
        .totals-box { border: 1px solid #ddd; border-radius: 4px; padding: 15px; width: 320px; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
        .totals-box .row:last-child { border-bottom: none; font-size: 16px; font-weight: bold; color: #1a56a7; }
        .terms-section { margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; }
        .terms-section h4 { color: #1a56a7; margin-bottom: 8px; }
        .terms-section p { color: #555; line-height: 1.6; }
        .print-btn { text-align: center; margin: 20px 0; }
        .print-btn button {
            background: #1a56a7; color: #fff; border: none; padding: 10px 30px;
            border-radius: 4px; font-size: 14px; cursor: pointer;
        }
        .footer-note { text-align: center; color: #aaa; font-size: 11px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        @media print {
            .print-btn { display: none; }
            body { font-size: 12px; }
            .print-page { padding: 10px 20px; }
        }
    </style>
</head>
<body>
<div class="print-page">

    <div class="print-btn">
        <button onclick="window.print()">🖨 طباعة</button>
    </div>

    {{-- Company Header --}}
    <div class="company-header">
        @php $setting = \App\Models\Admin_panel_setting::where('com_code', $quote->com_code ?? auth()->guard('admin')->user()->com_code ?? 1)->first(); @endphp
        @if($setting && $setting->image)
            <img src="{{ asset('storage/' . $setting->image) }}" alt="Logo" style="height:60px;margin-bottom:10px">
        @endif
        <h1>{{ $setting->com_name ?? 'اسم الشركة' }}</h1>
        <p>{{ $setting->address ?? '' }}</p>
        <p>{{ $setting->phone ?? '' }} | {{ $setting->email ?? '' }}</p>
        @if($setting->tax_number ?? null)
            <p>الرقم الضريبي: {{ $setting->tax_number }}</p>
        @endif
    </div>

    <div class="doc-title">عرض سعر</div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-box">
            <h4>بيانات عرض السعر</h4>
            <table>
                <tr><td>رقم العرض:</td><td>{{ $quote->quote_number }}</td></tr>
                <tr><td>التاريخ:</td><td>{{ \Carbon\Carbon::parse($quote->date)->format('Y/m/d') }}</td></tr>
                <tr><td>صالح حتى:</td><td>{{ \Carbon\Carbon::parse($quote->valid_until)->format('Y/m/d') }}</td></tr>
                <tr>
                    <td>الحالة:</td>
                    <td>
                        <span class="status-badge status-{{ $quote->status }}">
                            @switch($quote->status)
                                @case('draft') مسودة @break
                                @case('sent') مُرسَل @break
                                @case('accepted') مقبول @break
                                @case('rejected') مرفوض @break
                                @case('expired') منتهي @break
                            @endswitch
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="info-box">
            <h4>بيانات العميل</h4>
            <table>
                <tr><td>الاسم:</td><td>{{ $quote->customer->name ?? '—' }}</td></tr>
                <tr><td>الهاتف:</td><td>{{ $quote->customer->phone ?? '—' }}</td></tr>
                <tr><td>البريد:</td><td>{{ $quote->customer->email ?? '—' }}</td></tr>
                <tr><td>الرقم الضريبي:</td><td>{{ $quote->customer->tax_number ?? '—' }}</td></tr>
                <tr><td>العنوان:</td><td>{{ $quote->customer->address ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Items --}}
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>الوصف</th>
                <th>الوحدة</th>
                <th style="text-align:center">الكمية</th>
                <th style="text-align:left">السعر</th>
                <th style="text-align:center">خصم%</th>
                <th style="text-align:left">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quote->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item->name ?? $item->item_name ?? '—' }}</td>
                <td>{{ $item->description ?? '' }}</td>
                <td>{{ $item->unit->name ?? '' }}</td>
                <td style="text-align:center">{{ number_format($item->quantity, 3) }}</td>
                <td style="text-align:left">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:center">{{ $item->discount_percent ?? 0 }}%</td>
                <td style="text-align:left">{{ number_format($item->total, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#888">لا توجد بنود</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-section">
        <div class="totals-box">
            <div class="row"><span>الإجمالي الفرعي:</span><span>{{ number_format($quote->subtotal ?? 0, 2) }}</span></div>
            <div class="row"><span>الخصم:</span><span>- {{ number_format($quote->discount_amount ?? 0, 2) }}</span></div>
            <div class="row"><span>الضريبة ({{ $quote->tax_rate ?? 14 }}%):</span><span>{{ number_format($quote->tax_amount ?? 0, 2) }}</span></div>
            <div class="row"><span>الإجمالي الكلي:</span><span>{{ number_format($quote->total ?? 0, 2) }}</span></div>
        </div>
    </div>

    @if($quote->terms)
    <div class="terms-section">
        <h4>الشروط والأحكام</h4>
        <p>{{ $quote->terms }}</p>
    </div>
    @endif

    @if($quote->notes)
    <div class="terms-section">
        <h4>ملاحظات</h4>
        <p>{{ $quote->notes }}</p>
    </div>
    @endif

    <div class="footer-note">
        تم إنشاء هذا العرض بواسطة نظام NEXA ERP &mdash; {{ now()->format('Y/m/d H:i') }}
    </div>
</div>
</body>
</html>
