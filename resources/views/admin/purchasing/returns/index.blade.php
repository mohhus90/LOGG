@extends('admin.layouts.purchasing')
@section('title') المرتجعات @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_returns.index') }}">المرتجعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter ml-2"></i> تصفية النتائج</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('purchase_returns.index') }}">
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
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="">-- جميع الحالات --</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
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
                @if(request()->hasAny(['supplier_id','status','from','to']))
                    <a href="{{ route('purchase_returns.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> إلغاء التصفية</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-undo ml-2"></i> المرتجعات</h3>
            <div class="card-tools">
                <a href="{{ route('purchase_returns.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> إضافة مرتجع</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم المرتجع</th>
                            <th>التاريخ</th>
                            <th>المورد</th>
                            <th>الفاتورة المرتجع منها</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $return)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong class="text-primary">#{{ $return->return_number ?? $return->id }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($return->date)->format('Y/m/d') }}</td>
                            <td>{{ $return->supplier->name ?? '-' }}</td>
                            <td>
                                @if($return->invoice)
                                    <a href="{{ route('purchase_invoices.show', $return->invoice_id) }}">
                                        #{{ $return->invoice->invoice_number ?? $return->invoice_id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ number_format($return->total, 2) }}</strong> ج.م</td>
                            <td>{!! $return->status_label !!}</td>
                            <td>
                                <a href="{{ route('purchase_returns.show', $return->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($return->status === 'draft')
                                    <form action="{{ route('purchase_returns.approve', $return->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success"
                                                onclick="return confirm('اعتماد هذا المرتجع؟')" title="اعتماد">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('purchase_returns.reject', $return->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-warning"
                                                onclick="return confirm('رفض هذا المرتجع؟')" title="رفض">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('purchase_returns.delete', $return->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا المرتجع؟')" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد مرتجعات مسجلة
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
