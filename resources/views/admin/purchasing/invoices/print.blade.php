<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة شراء رقم {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', 'Tahoma', sans-serif; font-size: 13px; color: #222; direction: rtl; background: #fff; }
        .print-page { max-width: 850px; margin: 0 auto; padding: 30px 40px; }
        .company-header { text-align: center; border-bottom: 3px solid #8b5cf6; padding-bottom: 20px; margin-bottom: 20px; }
        .company-header h1 { font-size: 24px; color: #8b5cf6; margin-bottom: 5px; }
        .company-header p { color: #555; margin: 2px 0; }
        .doc-title { text-align: center; background: #8b5cf6; color: #fff; padding: 10px; font-size: 18px; font-weight: bold; margin-bottom: 20px; border-radius: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #ddd; border-radius: 4px; padding: 12px; }
        .info-box h4 { color: #8b5cf6; font-size: 13px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .info-box table { width: 100%; }
        .info-box table td { padding: 3px 0; }
        .info-box table td:first-child { color: #666; width: 130px; }
        .info-box table td:last-child { font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead { background: #8b5cf6; color: #fff; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 8px 10px; text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f5f7fa; }
        .totals-box { border: 1px solid #ddd; border-radius: 4px; padding: 15px; width: 300px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
        .totals-row:last-child { border-bottom: none; font-size: 15px; font-weight: bold; color: #8b5cf6; }
        .notes-section { margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; }
        .notes-section h4 { color: #8b5cf6; margin-bottom: 8px; }
        .print-btn { text-align: center; margin: 20px 0; }
        .print-btn button { background: #8b5cf6; color: #fff; border: none; padding: 10px 30px; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .footer-note { text-align: center; color: #aaa; font-size: 11px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        @media print { .print-btn { display: none; } .print-page { padding: 10px 20px; } }
    </style>
</head>
<body>
<div class="print-page">

    <div class="print-btn">
        <button onclick="window.print()">🖨 طباعة</button>
    </div>

    @php $setting = \App\Models\Admin_panel_setting::where('com_code', $invoice->com_code ?? auth()->guard('admin')->user()->com_code ?? 1)->first(); @endphp

    <div class="company-header">
        @if($setting && $setting->image)
            <img src="{{ asset('storage/' . $setting->image) }}" alt="Logo" style="height:60px;margin-bottom:10px">
        @endif
        <h1>{{ $setting->com_name ?? 'اسم الشركة' }}</h1>
        <p>{{ $setting->address ?? '' }}</p>
        <p>{{ $setting->phone ?? '' }}{{ ($setting->email ?? null) ? ' | ' . $setting->email : '' }}</p>
    </div>

    <div class="doc-title">فاتورة شراء</div>

    <div class="info-grid">
        <div class="info-box">
            <h4>بيانات الفاتورة</h4>
            <table>
                <tr><td>رقم الفاتورة:</td><td>{{ $invoice->invoice_number }}</td></tr>
                <tr><td>رقم فاتورة المورد:</td><td>{{ $invoice->supplier_invoice_no ?? '—' }}</td></tr>
                <tr><td>التاريخ:</td><td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td></tr>
                <tr><td>تاريخ الاستحقاق:</td><td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') : '—' }}</td></tr>
            </table>
        </div>
        <div class="info-box">
            <h4>بيانات المورد</h4>
            <table>
                <tr><td>الاسم:</td><td>{{ $invoice->supplier->name ?? '—' }}</td></tr>
                <tr><td>الهاتف:</td><td>{{ $invoice->supplier->phone ?? '—' }}</td></tr>
                <tr><td>البريد:</td><td>{{ $invoice->supplier->email ?? '—' }}</td></tr>
                <tr><td>الرقم الضريبي:</td><td>{{ $invoice->supplier->tax_number ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

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
            @forelse($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $item->item->name ?? '—' }}</strong></td>
                <td style="font-size:12px;color:#555">{{ $item->description ?? '' }}</td>
                <td>{{ $item->unit->name ?? '' }}</td>
                <td style="text-align:center">{{ number_format($item->quantity, 3) }}</td>
                <td style="text-align:left">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:center">{{ $item->discount_percent ?? 0 }}%</td>
                <td style="text-align:left"><strong>{{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#888">لا توجد بنود</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="display:flex;justify-content:flex-start">
        <div class="totals-box">
            <div class="totals-row"><span>الإجمالي الفرعي:</span><span>{{ number_format($invoice->subtotal ?? 0, 2) }}</span></div>
            <div class="totals-row"><span>الخصم:</span><span>- {{ number_format($invoice->discount_amount ?? 0, 2) }}</span></div>
            <div class="totals-row"><span>الضريبة ({{ $invoice->tax_rate ?? 14 }}%):</span><span>{{ number_format($invoice->tax_amount ?? 0, 2) }}</span></div>
            <div class="totals-row"><span>الإجمالي الكلي:</span><span>{{ number_format($invoice->total ?? 0, 2) }}</span></div>
        </div>
    </div>

    @if($invoice->notes)
    <div class="notes-section">
        <h4>ملاحظات</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer-note">
        تم إنشاء هذه الفاتورة بواسطة نظام NEXA ERP &mdash; {{ now()->format('Y/m/d H:i') }}
    </div>
</div>
</body>
</html>
