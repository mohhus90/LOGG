<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة رقم {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', 'Tahoma', sans-serif; font-size: 13px; color: #222; direction: rtl; background: #fff; }
        .print-page { max-width: 850px; margin: 0 auto; padding: 30px 40px; }

        /* Company Header */
        .company-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1a56a7; padding-bottom: 20px; margin-bottom: 20px; }
        .company-header .company-info h1 { font-size: 22px; color: #1a56a7; margin-bottom: 4px; }
        .company-header .company-info p { color: #555; margin: 2px 0; font-size: 12px; }
        .company-header .logo img { max-height: 70px; max-width: 150px; object-fit: contain; }
        .invoice-title-box { text-align: center; }
        .invoice-title-box h2 { font-size: 28px; color: #1a56a7; font-weight: bold; letter-spacing: 2px; }
        .invoice-title-box .invoice-num { font-size: 14px; color: #555; margin-top: 4px; }

        /* Status */
        .invoice-status { display: inline-block; padding: 4px 14px; border-radius: 12px; font-size: 12px; font-weight: bold; color: #fff; }
        .status-issued    { background: #007bff; }
        .status-draft     { background: #6c757d; }
        .status-cancelled { background: #dc3545; }
        .pay-paid    { background: #28a745; }
        .pay-partial { background: #ffc107; color: #333; }
        .pay-unpaid  { background: #dc3545; }

        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { border: 1px solid #ddd; border-radius: 4px; padding: 14px; }
        .info-box h4 { color: #1a56a7; font-size: 13px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }
        .info-box table { width: 100%; }
        .info-box table td { padding: 4px 0; vertical-align: top; }
        .info-box table td:first-child { color: #666; width: 130px; font-size: 12px; }
        .info-box table td:last-child { font-weight: 600; }

        /* Items Table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead { background: #1a56a7; color: #fff; }
        table.items th { padding: 10px; text-align: right; font-size: 12px; font-weight: 600; }
        table.items td { padding: 9px 10px; border-bottom: 1px solid #eee; text-align: right; }
        table.items tbody tr:nth-child(even) { background: #f8f9fa; }
        table.items tbody tr:last-child td { border-bottom: 2px solid #1a56a7; }

        /* Totals */
        .totals-wrapper { display: flex; justify-content: flex-end; margin-bottom: 25px; }
        .totals-box { border: 1px solid #dee2e6; border-radius: 6px; width: 320px; overflow: hidden; }
        .totals-box .totals-row { display: flex; justify-content: space-between; padding: 8px 16px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        .totals-box .totals-row:nth-child(odd) { background: #f8f9fa; }
        .totals-box .totals-row:last-child { border-bottom: none; background: #1a56a7; color: #fff; font-size: 15px; font-weight: bold; padding: 12px 16px; }
        .totals-box .totals-row.paid-row { background: #d4edda; color: #155724; font-weight: bold; }
        .totals-box .totals-row.remaining-row { background: #f8d7da; color: #721c24; font-weight: bold; font-size: 14px; }

        /* Payments History */
        table.payments { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        table.payments thead { background: #28a745; color: #fff; }
        table.payments th, table.payments td { padding: 7px 10px; border: 1px solid #ddd; text-align: right; }
        table.payments tbody tr:nth-child(even) { background: #f5f5f5; }

        /* Notes */
        .notes-box { border: 1px solid #dee2e6; border-radius: 4px; padding: 14px; margin-bottom: 20px; }
        .notes-box h4 { color: #1a56a7; font-size: 12px; margin-bottom: 8px; }
        .notes-box p { color: #555; line-height: 1.6; font-size: 12px; }

        /* Footer */
        .invoice-footer { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
        .signature-box { text-align: center; width: 200px; }
        .signature-box .line { border-bottom: 1px solid #aaa; margin-bottom: 6px; height: 40px; }
        .signature-box p { font-size: 11px; color: #666; }
        .footer-note { text-align: center; color: #aaa; font-size: 11px; margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; }

        /* Print Button */
        .print-btn { text-align: center; margin: 20px 0; }
        .print-btn button { background: #1a56a7; color: #fff; border: none; padding: 10px 30px; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .print-btn button:hover { background: #154391; }

        @media print {
            .print-btn { display: none; }
            body { font-size: 12px; }
            .print-page { padding: 5mm 10mm; max-width: none; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body>
<div class="print-page">

    <div class="print-btn">
        <button onclick="window.print()">🖨 طباعة الفاتورة</button>
    </div>

    @php
        $setting   = \App\Models\Admin_panel_setting::where('com_code', $invoice->com_code ?? auth()->guard('admin')->user()->com_code ?? 1)->first();
        $paid      = $invoice->paid_amount ?? 0;
        $remaining = max(0, $invoice->total - $paid);
        $payStatus = $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
    @endphp

    {{-- Company Header --}}
    <div class="company-header">
        <div class="company-info">
            <h1>{{ $setting->com_name ?? 'اسم الشركة' }}</h1>
            @if($setting->address ?? null) <p><i>📍</i> {{ $setting->address }}</p> @endif
            @if($setting->phone   ?? null) <p><i>📞</i> {{ $setting->phone }}</p> @endif
            @if($setting->email   ?? null) <p><i>✉</i> {{ $setting->email }}</p> @endif
            @if($setting->tax_number ?? null) <p>الرقم الضريبي: <strong>{{ $setting->tax_number }}</strong></p> @endif
        </div>
        <div class="invoice-title-box">
            @if($setting && $setting->image)
                <div class="logo"><img src="{{ asset('storage/' . $setting->image) }}" alt="Logo"></div>
            @else
                <h2>فاتورة</h2>
                <div class="invoice-num">رقم: {{ $invoice->invoice_number }}</div>
            @endif
        </div>
    </div>

    @if($setting && $setting->image)
    <div style="text-align:center;margin-bottom:20px">
        <h2 style="font-size:26px;color:#1a56a7;font-weight:bold">فـاتـورة مبيعات</h2>
        <div style="font-size:13px;color:#555;margin-top:4px">رقم الفاتورة: <strong>{{ $invoice->invoice_number }}</strong></div>
    </div>
    @endif

    {{-- Status Badges --}}
    <div style="text-align:left;margin-bottom:16px">
        @switch($invoice->status ?? 'issued')
            @case('issued')    <span class="invoice-status status-issued">مُصدَرة</span> @break
            @case('draft')     <span class="invoice-status status-draft">مسودة</span> @break
            @case('cancelled') <span class="invoice-status status-cancelled">ملغاة</span> @break
        @endswitch
        &nbsp;
        @switch($payStatus)
            @case('paid')    <span class="invoice-status pay-paid">مسدّد بالكامل</span> @break
            @case('partial') <span class="invoice-status pay-partial">مسدّد جزئياً</span> @break
            @case('unpaid')  <span class="invoice-status pay-unpaid">غير مسدّد</span> @break
        @endswitch
        &nbsp;
        @if(($invoice->invoice_type ?? '') === 'cash')
            <span class="invoice-status" style="background:#28a745">نقدي</span>
        @else
            <span class="invoice-status" style="background:#17a2b8">آجل</span>
        @endif
    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-box">
            <h4>بيانات العميل</h4>
            <table>
                <tr><td>الاسم:</td><td>{{ $invoice->customer->name ?? '—' }}</td></tr>
                <tr><td>الهاتف:</td><td>{{ $invoice->customer->phone ?? '—' }}</td></tr>
                <tr><td>البريد:</td><td>{{ $invoice->customer->email ?? '—' }}</td></tr>
                <tr><td>الرقم الضريبي:</td><td>{{ $invoice->customer->tax_number ?? '—' }}</td></tr>
                <tr><td>العنوان:</td><td>{{ $invoice->customer->address ?? '—' }}</td></tr>
            </table>
        </div>
        <div class="info-box">
            <h4>بيانات الفاتورة</h4>
            <table>
                <tr><td>رقم الفاتورة:</td><td>{{ $invoice->invoice_number }}</td></tr>
                <tr><td>التاريخ:</td><td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td></tr>
                <tr><td>تاريخ الاستحقاق:</td><td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') : '—' }}</td></tr>
                <tr><td>الفرع:</td><td>{{ $invoice->branch->name ?? '—' }}</td></tr>
                @if($invoice->order_id)
                <tr><td>أمر البيع:</td><td>{{ $invoice->order->order_number ?? $invoice->order_id }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:35px">#</th>
                <th>الصنف</th>
                <th>الوصف</th>
                <th>الوحدة</th>
                <th style="text-align:center">الكمية</th>
                <th style="text-align:left">سعر الوحدة</th>
                <th style="text-align:center">الخصم%</th>
                <th style="text-align:left">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $i => $item)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td><strong>{{ $item->item->name ?? $item->item_name ?? '—' }}</strong></td>
                <td style="font-size:12px;color:#555">{{ $item->description ?? '' }}</td>
                <td>{{ $item->unit->name ?? '' }}</td>
                <td style="text-align:center">{{ number_format($item->quantity, 3) }}</td>
                <td style="text-align:left">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:center">{{ $item->discount_percent ?? 0 }}%</td>
                <td style="text-align:left"><strong>{{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#888;padding:20px">لا توجد بنود</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals-wrapper">
        <div class="totals-box">
            <div class="totals-row">
                <span>الإجمالي الفرعي</span>
                <span>{{ number_format($invoice->subtotal ?? 0, 2) }}</span>
            </div>
            @if(($invoice->discount_amount ?? 0) > 0)
            <div class="totals-row">
                <span>الخصم</span>
                <span>- {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row">
                <span>الضريبة ({{ $invoice->tax_rate ?? 14 }}%)</span>
                <span>{{ number_format($invoice->tax_amount ?? 0, 2) }}</span>
            </div>
            <div class="totals-row" style="font-size:15px;font-weight:bold;background:#e8f0fb;color:#1a56a7">
                <span>الإجمالي الكلي</span>
                <span>{{ number_format($invoice->total ?? 0, 2) }}</span>
            </div>
            @if($paid > 0)
            <div class="totals-row paid-row">
                <span>المحصّل</span>
                <span>{{ number_format($paid, 2) }}</span>
            </div>
            @endif
            @if($remaining > 0)
            <div class="totals-row remaining-row">
                <span>المتبقي</span>
                <span>{{ number_format($remaining, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment History (if any) --}}
    @if($invoice->payments && $invoice->payments->count() > 0)
    <div style="margin-bottom:20px">
        <h4 style="color:#1a56a7;margin-bottom:10px;font-size:13px">سجل الدفعات</h4>
        <table class="payments">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>رقم الإيصال</th>
                    <th>طريقة الدفع</th>
                    <th style="text-align:left">المبلغ</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $i => $payment)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}</td>
                    <td>{{ $payment->receipt_number ?? '—' }}</td>
                    <td>
                        @switch($payment->payment_method ?? '')
                            @case('cash')          نقدي @break
                            @case('bank_transfer')  تحويل بنكي @break
                            @case('check')         شيك @break
                            @case('card')          بطاقة @break
                            @default {{ $payment->payment_method ?? '—' }}
                        @endswitch
                    </td>
                    <td style="text-align:left;color:green;font-weight:bold">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->notes ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Notes --}}
    @if($invoice->notes)
    <div class="notes-box">
        <h4>ملاحظات</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="invoice-footer">
        <div class="signature-box">
            <div class="line"></div>
            <p>توقيع المحاسب</p>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <p>توقيع العميل / المستلم</p>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <p>الختم الرسمي</p>
        </div>
    </div>

    <div class="footer-note">
        تم إصدار هذه الفاتورة بواسطة نظام NEXA ERP &mdash; {{ now()->format('Y/m/d H:i') }}
        @if($setting->website ?? null) &mdash; {{ $setting->website }} @endif
    </div>
</div>
</body>
</html>
