@extends('admin.layouts.purchasing')
@section('title') تقرير المستحقات @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_reports.index') }}">التقارير</a> @endsection
@section('startpage') المستحقات @endsection

@section('content')
<div class="col-12">

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($totalDebt, 2) }} <small style="font-size:14px">ج.م</small></h3>
                    <p>إجمالي المستحقات للموردين</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search ml-2"></i> بحث عن مورد</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchase_reports.debt') }}">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="ابحث باسم المورد أو الكود...">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-start">
                        <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-search"></i> بحث</button>
                        @if(request('search'))
                            <a href="{{ route('purchase_reports.debt') }}" class="btn btn-secondary"><i class="fas fa-times"></i> مسح</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-hand-holding-usd ml-2"></i> تقرير المستحقات - الفواتير غير المسددة</h3>
            <div class="card-tools">
                <a href="{{ route('purchase_reports.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body">

            @forelse($data as $supplier)
            @php
                $supplierTotal = collect($supplier->invoices)->sum(fn($inv) => $inv->remaining_amount ?? 0);
            @endphp
            <div class="card card-light mb-3">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck ml-2 text-danger"></i>
                        <strong>{{ $supplier->name ?? '-' }}</strong>
                        @if($supplier->code ?? null)
                            <small class="text-muted mr-2">{{ $supplier->code }}</small>
                        @endif
                    </h5>
                    <span class="badge badge-danger px-3 py-2" style="font-size:.95em;">
                        إجمالي المستحق: {{ number_format($supplierTotal, 2) }} ج.م
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>التاريخ</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>تاريخ الاستحقاق</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplier->invoices as $invoice)
                                @php
                                    $isOverdue = isset($invoice->due_date) && $invoice->due_date && \Carbon\Carbon::parse($invoice->due_date)->isPast();
                                @endphp
                                <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                    <td>
                                        <a href="{{ route('purchase_invoices.show', $invoice->id) }}" class="{{ $isOverdue ? 'text-danger' : 'text-primary' }}">
                                            <strong>#{{ $invoice->invoice_number }}</strong>
                                        </a>
                                        @if($isOverdue)
                                            <span class="badge badge-danger mr-1 badge-sm">متأخر</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}</td>
                                    <td>{{ number_format($invoice->total ?? 0, 2) }} ج.م</td>
                                    <td class="text-success">{{ number_format($invoice->paid_amount ?? 0, 2) }} ج.م</td>
                                    <td class="text-danger font-weight-bold">{{ number_format($invoice->remaining_amount ?? 0, 2) }} ج.م</td>
                                    <td>
                                        @if($invoice->due_date ?? null)
                                            <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}
                                                @if($isOverdue)
                                                    <i class="fas fa-clock mr-1"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x text-success mb-3 d-block"></i>
                <h5>لا توجد مستحقات</h5>
                <p>جميع الفواتير مسددة</p>
            </div>
            @endforelse

        </div>
    </div>
</div>
@endsection
