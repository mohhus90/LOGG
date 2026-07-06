@extends('admin.layouts.admin')
@section('title') السلف @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('advances.index') }}">السلف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-hand-holding-usd ml-2"></i>
                سجل السلف
                <a href="{{ route('advances.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة سلف
                </a>
                <a href="{{ route('advances.copy_month_form') }}" class="btn btn-sm btn-info mr-2">
                    <i class="fas fa-copy"></i> نسخ من شهر سابق
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('advances.index') }}" class="form-inline mb-3">
                <select name="employee_id" class="form-control ml-2">
                    <option value="">-- كل الموظفين --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>
                        {{ $emp->employee_name_A }}
                    </option>
                    @endforeach
                </select>
                <select name="status" class="form-control ml-2">
                    <option value="">-- كل الحالات --</option>
                    <option value="1" {{ request('status')==1?'selected':'' }}>جارية</option>
                    <option value="2" {{ request('status')==2?'selected':'' }}>مسددة</option>
                    <option value="3" {{ request('status')==3?'selected':'' }}>ملغاة</option>
                </select>
                <select name="month" class="form-control ml-2">
                    <option value="">-- كل الشهور --</option>
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                    <option value="{{ $i+1 }}" {{ request('month')==$i+1?'selected':'' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <input type="number" name="year" class="form-control ml-2" style="width:90px"
                    placeholder="السنة" value="{{ request('year') }}">
                <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('advances.index') }}" class="btn btn-secondary">مسح</a>
            </form>
        </div>

        {{-- مجاميع السلف --}}
        <div class="row mx-3 mb-3">
            <div class="col-sm-6 col-md-4">
                <div class="info-box shadow-sm mb-2">
                    <span class="info-box-icon bg-warning"><i class="fas fa-filter"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">إجمالي السلف المعروضة</span>
                        <span class="info-box-number">{{ number_format($filteredTotal, 2) }} <small>ج.م</small></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="info-box shadow-sm mb-2">
                    <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">سلف شهر {{ now()->translatedFormat('F Y') }}</span>
                        <span class="info-box-number">{{ number_format($monthTotal, 2) }} <small>ج.م</small></span>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3">{{ session('success') }}</div>
        @endif

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>التاريخ</th>
                            <th>قيمة السلفة</th>
                            <th>عدد الأقساط</th>
                            <th>القسط الشهري</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>ملاحظات</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $adv)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $adv->employee->employee_name_A ?? '-' }}</td>
                            <td>{{ $adv->advance_date }}</td>
                            <td class="font-weight-bold">{{ number_format($adv->amount, 2) }}</td>
                            <td class="text-center">{{ $adv->installments }}</td>
                            <td>{{ number_format($adv->monthly_installment, 2) }}</td>
                            <td>
                                <span class="{{ $adv->remaining_amount > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                                    {{ number_format($adv->remaining_amount, 2) }}
                                </span>
                            </td>
                            <td>{!! $adv->status_label !!}</td>
                            <td><small>{{ $adv->notes }}</small></td>
                            <td>
                                <a href="{{ route('advances.edit', $adv->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('advances.delete', $adv->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('حذف هذه السلفة؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center">لا توجد سلف مسجلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
