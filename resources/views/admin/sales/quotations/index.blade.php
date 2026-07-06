@extends('admin.layouts.sales')
@section('title') عروض الأسعار @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_quotations.index') }}">عروض الأسعار</a> @endsection
@section('startpage') عرض الكل @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> عروض الأسعار</h3>
            <div class="card-tools">
                <a href="{{ route('sales_quotations.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة عرض سعر
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('sales_quotations.index') }}">
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
                                <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>مسودة</option>
                                <option value="sent"     {{ request('status') == 'sent'     ? 'selected' : '' }}>مُرسَل</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="expired"  {{ request('status') == 'expired'  ? 'selected' : '' }}>منتهي الصلاحية</option>
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
                        <a href="{{ route('sales_quotations.index') }}" class="btn btn-secondary btn-sm">
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
                            <th>رقم العرض</th>
                            <th>التاريخ</th>
                            <th>صلاحية لغاية</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th style="width:200px">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $quote)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $quote->quote_number }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($quote->date)->format('Y/m/d') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($quote->valid_until)->format('Y/m/d') }}
                                @if($quote->status !== 'accepted' && \Carbon\Carbon::parse($quote->valid_until)->isPast())
                                    <span class="badge badge-warning mr-1">منتهي</span>
                                @endif
                            </td>
                            <td>{{ $quote->customer->name ?? '—' }}</td>
                            <td class="text-left"><strong>{{ number_format($quote->total, 2) }}</strong></td>
                            <td>
                                @switch($quote->status)
                                    @case('draft')
                                        <span class="badge badge-secondary">مسودة</span>
                                        @break
                                    @case('sent')
                                        <span class="badge badge-info">مُرسَل</span>
                                        @break
                                    @case('accepted')
                                        <span class="badge badge-success">مقبول</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge badge-danger">مرفوض</span>
                                        @break
                                    @case('expired')
                                        <span class="badge badge-warning">منتهي الصلاحية</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $quote->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('sales_quotations.show', $quote->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales_quotations.edit', $quote->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('sales_quotations.print', $quote->id) }}" class="btn btn-xs btn-secondary" target="_blank" title="طباعة">
                                    <i class="fas fa-print"></i>
                                </a>
                                <form action="{{ route('sales_quotations.convert', $quote->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل تريد تحويل هذا العرض إلى أمر بيع؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success" title="تحويل لأمر بيع">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                                <a href="{{ route('sales_quotations.delete', $quote->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا العرض؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد عروض أسعار مسجلة
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
