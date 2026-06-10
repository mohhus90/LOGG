@extends('admin.layouts.admin')
@section('title') الجزاءات @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="#">الجزاءات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header" style="background:#dc3545;color:#fff">
    <h3 class="card-title">
      <i class="fas fa-gavel ml-2"></i>
      سجل الجزاءات
      <a href="{{ route('sanctions.create') }}" class="btn btn-sm btn-light mr-2">
        <i class="fas fa-plus"></i> إضافة جزاء
      </a>
    </h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-3 alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif

  {{-- فلاتر --}}
  <div class="card-body pb-0">
    <form method="GET" class="row">
      <div class="col-md-3 form-group">
        <label>الموظف</label>
        <select name="employee_id" class="form-control select2">
          <option value="">-- الكل --</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>
              {{ $emp->employee_name_A }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 form-group">
        <label>النوع</label>
        <select name="type" class="form-control">
          <option value="">-- الكل --</option>
          <option value="1" {{ request('type')==1?'selected':'' }}>تحذير</option>
          <option value="2" {{ request('type')==2?'selected':'' }}>إنذار رسمي</option>
          <option value="3" {{ request('type')==3?'selected':'' }}>خصم مالي</option>
          <option value="4" {{ request('type')==4?'selected':'' }}>إيقاف عن العمل</option>
          <option value="5" {{ request('type')==5?'selected':'' }}>خصم باليوم</option>
        </select>
      </div>
      <div class="col-md-2 form-group">
        <label>من تاريخ</label>
        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
      </div>
      <div class="col-md-2 form-group">
        <label>إلى تاريخ</label>
        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
      </div>
      <div class="col-md-2 form-group">
        <label>الحالة</label>
        <select name="status" class="form-control">
          <option value="1"   {{ request('status','1')=='1'   ? 'selected' : '' }}>فعّال</option>
          <option value="0"   {{ request('status')=='0'        ? 'selected' : '' }}>ملغى</option>
          <option value="all" {{ request('status')=='all'      ? 'selected' : '' }}>الكل</option>
        </select>
      </div>
      <div class="col-md-1 form-group d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>
  </div>

  <div class="card-body pt-0">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm">
        <thead class="thead-dark">
          <tr>
            <th>#</th>
            <th>الموظف</th>
            <th>النوع</th>
            <th>المبلغ / الأيام</th>
            <th>التاريخ</th>
            <th>شهر الاستقطاع</th>
            <th>الوصف</th>
            <th>الحالة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $s)
          <tr class="{{ $s->status == 0 ? 'table-secondary' : '' }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $s->employee->employee_name_A ?? '-' }}</td>
            <td>{!! $s->getTypeBadge() !!}</td>
            <td>
              @if($s->type == 3)
                <span class="text-danger font-weight-bold">{{ number_format($s->amount, 2) }} ج.م</span>
              @elseif($s->type == 4)
                <span class="text-dark">{{ $s->suspension_days }} يوم إيقاف</span>
              @elseif($s->type == 5)
                <span class="text-info font-weight-bold">{{ number_format($s->deduct_days, 2) }} يوم</span>
                <br><small class="text-muted">يُحتسب من الراتب</small>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>{{ $s->date?->format('Y-m-d') }}</td>
            <td>{{ $s->deduct_month ?? '—' }}</td>
            <td><small>{{ Str::limit($s->description, 50) }}</small></td>
            <td>
              @if($s->status == 1)
                <span class="badge badge-success">فعّال</span>
              @else
                <span class="badge badge-secondary">ملغى</span>
              @endif
            </td>
            <td>
              <a href="{{ route('sanctions.edit', $s->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                <i class="fas fa-edit"></i>
              </a>
              @if($s->status == 1)
                <form action="{{ route('sanctions.cancel', $s->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('إلغاء هذا الجزاء؟')">
                  @csrf
                  <button type="submit" class="btn btn-xs btn-secondary" title="إلغاء">
                    <i class="fas fa-ban"></i>
                  </button>
                </form>
              @endif
              <form action="{{ route('sanctions.delete', $s->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('حذف نهائي؟')">
                @csrf
                <button type="submit" class="btn btn-xs btn-danger" title="حذف">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="9" class="text-center text-muted">لا توجد جزاءات</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $data->withQueryString()->links() }}
  </div>
</div>
</div>
@endsection
