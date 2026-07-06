@extends('admin.layouts.sales')
@section('title') المدفوعات @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_payments.index') }}">المدفوعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    {{-- Stat Box --}}
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

    {{-- Filters --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter ml-2"></i> تصفية النتائج</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales_payments.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>العميل</label>
                            <select name="customer_id" class="form-control select2">
                                <option value="">-- جميع العملاء --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
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
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['customer_id','payment_method','from','to']))
                    <a href="{{ route('sales_payments.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times"></i> إلغاء التصفية
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave ml-2"></i> المدفوعات</h3>
            <div class="card-tools">
                <a href="{{ route('sales_payments.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> إضافة دفعة
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success mx-3 mt-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mx-3 mt-3">{{ session('error') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم الدفعة</th>
                            <th>التاريخ</th>
                            <th>العميل</th>
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
                            <td>{{ $payment->customer->name ?? '-' }}</td>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('sales_invoices.show', $payment->invoice_id) }}">
                                        #{{ $payment->invoice->invoice_number ?? $payment->invoice_id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($payment->amount, 2) }}</strong> ج.م</td>
                            <td>
                                @switch($payment->payment_method)
                                    @case('cash')
                                        <span class="badge badge-success"><i class="fas fa-money-bill-alt ml-1"></i>نقدي</span>
                                        @break
                                    @case('bank')
                                        <span class="badge badge-primary"><i class="fas fa-university ml-1"></i>بنكي</span>
                                        @break
                                    @case('cheque')
                                        <span class="badge badge-warning text-dark"><i class="fas fa-money-check ml-1"></i>شيك</span>
                                        @break
                                    @case('pos')
                                        <span class="badge badge-info"><i class="fas fa-credit-card ml-1"></i>بطاقة</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $payment->payment_method }}</span>
                                @endswitch
                            </td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                            <td>
                                <a href="{{ route('sales_payments.show', $payment->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales_payments.delete', $payment->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('هل أنت متأكد من حذف هذه الدفعة؟')" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد مدفوعات مسجلة
                            </td>
                        </tr>
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
