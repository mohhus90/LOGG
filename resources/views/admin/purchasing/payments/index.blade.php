@extends('admin.layouts.purchasing')
@section('title') المدفوعات @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_payments.index') }}">المدفوعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalAmount, 2) }} <small style="font-size:14px">ج.م</small></h3>
                    <p>إجمالي المدفوعات</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter ml-2"></i> تصفية النتائج</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchase_payments.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>المورد</label>
                            <select name="supplier_id" class="form-control select2">
                                <option value="">-- جميع الموردين --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>طريقة الدفع</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- جميع الطرق --</option>
                                <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                                <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>بنكي</option>
                                <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>شيك</option>
                                <option value="pos" {{ request('payment_method') == 'pos' ? 'selected' : '' }}>بطاقة</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> بحث</button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['supplier_id','payment_method','from','to']))
                    <a href="{{ route('purchase_payments.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> إلغاء التصفية</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave ml-2"></i> المدفوعات</h3>
            <div class="card-tools">
                <a href="{{ route('purchase_payments.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> إضافة دفعة</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم الدفعة</th>
                            <th>التاريخ</th>
                            <th>المورد</th>
                            <th>الفاتورة</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>مرجع</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong class="text-primary">#{{ $payment->payment_number ?? $payment->id }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($payment->date)->format('Y/m/d') }}</td>
                            <td>{{ $payment->supplier->name ?? '-' }}</td>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('purchase_invoices.show', $payment->invoice_id) }}">
                                        #{{ $payment->invoice->invoice_number ?? $payment->invoice_id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($payment->amount, 2) }}</strong> ج.م</td>
                            <td>{!! $payment->method_label !!}</td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td>
                                <a href="{{ route('purchase_payments.show', $payment->id) }}" class="btn btn-xs btn-info" title="عرض"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('purchase_payments.delete', $payment->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('هل أنت متأكد من حذف هذه الدفعة؟')" title="حذف"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>لا توجد مدفوعات مسجلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
