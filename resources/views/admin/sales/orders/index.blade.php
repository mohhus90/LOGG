@extends('admin.layouts.sales')
@section('title') أوامر البيع @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_orders.index') }}">أوامر البيع</a> @endsection
@section('startpage') عرض الكل @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-cart ml-2"></i> أوامر البيع</h3>
            <div class="card-tools">
                <a href="{{ route('sales_orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة أمر بيع
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('sales_orders.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small text-muted">العميل</label>
                            <select name="customer_id" class="form-control form-control-sm select2">
                                <option value="">-- كل العملاء --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">الحالة</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">-- كل الحالات --</option>
                                <option value="draft"       {{ request('status') == 'draft'       ? 'selected' : '' }}>مسودة</option>
                                <option value="confirmed"   {{ request('status') == 'confirmed'   ? 'selected' : '' }}>مؤكد</option>
                                <option value="processing"  {{ request('status') == 'processing'  ? 'selected' : '' }}>قيد التنفيذ</option>
                                <option value="partial"     {{ request('status') == 'partial'     ? 'selected' : '' }}>تسليم جزئي</option>
                                <option value="delivered"   {{ request('status') == 'delivered'   ? 'selected' : '' }}>مُسلَّم</option>
                                <option value="cancelled"   {{ request('status') == 'cancelled'   ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">من تاريخ</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="small text-muted">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm ml-1">
                            <i class="fas fa-search"></i> بحث
                        </button>
                        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> إعادة تعيين
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>رقم الأمر</th>
                            <th>المصدر</th>
                            <th>التاريخ</th>
                            <th>تاريخ التسليم</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th style="width:200px">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>
                                @if($order->source === 'wuilt')
                                    <span class="badge badge-info" title="{{ $order->external_order_id }}"><i class="fas fa-store"></i> Wuilt</span>
                                    @if($order->needs_item_mapping)
                                        <span class="badge badge-warning" title="بعض البنود غير مطابقة مع أصناف النظام">تحتاج مطابقة</span>
                                    @endif
                                @else
                                    <span class="text-muted">يدوي</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->date)->format('Y/m/d') }}</td>
                            <td>
                                @if($order->delivery_date)
                                    {{ \Carbon\Carbon::parse($order->delivery_date)->format('Y/m/d') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $order->customer->name ?? '—' }}</td>
                            <td class="text-left"><strong>{{ number_format($order->total, 2) }}</strong></td>
                            <td>
                                @switch($order->status)
                                    @case('draft')       <span class="badge badge-secondary">مسودة</span> @break
                                    @case('confirmed')   <span class="badge badge-primary">مؤكد</span> @break
                                    @case('processing')  <span class="badge badge-info">قيد التنفيذ</span> @break
                                    @case('partial')     <span class="badge badge-warning">تسليم جزئي</span> @break
                                    @case('delivered')   <span class="badge badge-success">مُسلَّم</span> @break
                                    @case('cancelled')   <span class="badge badge-danger">ملغي</span> @break
                                    @default             <span class="badge badge-secondary">{{ $order->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('sales_orders.show', $order->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales_orders.edit', $order->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('sales_orders.print', $order->id) }}" class="btn btn-xs btn-secondary" target="_blank" title="طباعة">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if(in_array($order->status, ['confirmed', 'processing']))
                                <form action="{{ route('sales_orders.invoice', $order->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('إنشاء فاتورة لهذا الأمر؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-primary" title="إنشاء فاتورة">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('sales_orders.delete', $order->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا الأمر؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد أوامر بيع مسجلة
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

@section('script')
<script>
$(document).ready(function() {
    $('select.select2').select2({ language: 'ar', width: '100%' });
});
</script>
@endsection
