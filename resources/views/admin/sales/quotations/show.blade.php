@extends('admin.layouts.sales')
@section('title') عرض سعر {{ $quote->quote_number }} @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_quotations.index') }}">عروض الأسعار</a> @endsection
@section('startpage') عرض التفاصيل @endsection

@section('content')
<div class="col-12">

    {{-- Action Bar --}}
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('sales_quotations.edit', $quote->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <a href="{{ route('sales_quotations.print', $quote->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                <i class="fas fa-print ml-1"></i> طباعة
            </a>
            <form action="{{ route('sales_quotations.convert', $quote->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('تحويل هذا العرض إلى أمر بيع؟')">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-exchange-alt ml-1"></i> تحويل لأمر بيع
                </button>
            </form>
            {{-- Change Status Dropdown --}}
            <div class="btn-group mr-1">
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-tag ml-1"></i> تغيير الحالة
                </button>
                <div class="dropdown-menu">
                    @foreach(['sent' => 'مُرسَل', 'accepted' => 'مقبول', 'rejected' => 'مرفوض', 'expired' => 'منتهي الصلاحية'] as $val => $label)
                        @if($quote->status !== $val)
                        <form action="{{ route('sales_quotations.status', $quote->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $val }}">
                            <button type="submit" class="dropdown-item">{{ $label }}</button>
                        </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div>
            @switch($quote->status)
                @case('draft')    <span class="badge badge-secondary badge-lg p-2" style="font-size:1rem">مسودة</span> @break
                @case('sent')     <span class="badge badge-info    badge-lg p-2" style="font-size:1rem">مُرسَل</span> @break
                @case('accepted') <span class="badge badge-success badge-lg p-2" style="font-size:1rem">مقبول</span> @break
                @case('rejected') <span class="badge badge-danger  badge-lg p-2" style="font-size:1rem">مرفوض</span> @break
                @case('expired')  <span class="badge badge-warning badge-lg p-2" style="font-size:1rem">منتهي الصلاحية</span> @break
            @endswitch
        </div>
    </div>

    {{-- Two-Column Info --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user ml-2"></i> بيانات العميل</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th style="width:130px" class="text-muted">العميل</th>
                            <td><strong>{{ $quote->customer->name ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">رقم الهاتف</th>
                            <td>{{ $quote->customer->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">البريد الإلكتروني</th>
                            <td>{{ $quote->customer->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">العنوان</th>
                            <td>{{ $quote->customer->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">الرقم الضريبي</th>
                            <td>{{ $quote->customer->tax_number ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> بيانات عرض السعر</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th style="width:130px" class="text-muted">رقم العرض</th>
                            <td><strong>{{ $quote->quote_number }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">التاريخ</th>
                            <td>{{ \Carbon\Carbon::parse($quote->date)->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">صالح حتى</th>
                            <td>{{ \Carbon\Carbon::parse($quote->valid_until)->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">الفرع</th>
                            <td>{{ $quote->branch->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">أنشئ بواسطة</th>
                            <td>{{ $quote->creator->name ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود العرض</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الوصف</th>
                            <th>الوحدة</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-left">السعر</th>
                            <th class="text-center">الخصم%</th>
                            <th class="text-left">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quote->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $item->item->name ?? $item->item_name ?? '—' }}</strong></td>
                            <td>{{ $item->description ?? '—' }}</td>
                            <td>{{ $item->unit->name ?? '—' }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                            <td class="text-left">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->discount_percent ?? 0 }}%</td>
                            <td class="text-left"><strong>{{ number_format($item->total, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">لا توجد بنود</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Totals + Notes --}}
    <div class="row">
        <div class="col-md-6">
            @if($quote->notes || $quote->terms)
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات وشروط</h3>
                </div>
                <div class="card-body">
                    @if($quote->notes)
                        <p class="mb-1"><strong>ملاحظات:</strong></p>
                        <p class="text-muted">{{ $quote->notes }}</p>
                    @endif
                    @if($quote->terms)
                        <p class="mb-1"><strong>الشروط والأحكام:</strong></p>
                        <p class="text-muted mb-0">{{ $quote->terms }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">الإجمالي الفرعي:</td>
                            <td class="text-left"><strong>{{ number_format($quote->subtotal ?? 0, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">الخصم:</td>
                            <td class="text-left text-danger">- {{ number_format($quote->discount_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">الضريبة ({{ $quote->tax_rate ?? 14 }}%):</td>
                            <td class="text-left">{{ number_format($quote->tax_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><strong class="h6">الإجمالي الكلي:</strong></td>
                            <td class="text-left"><strong class="h5 text-primary">{{ number_format($quote->total ?? 0, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
