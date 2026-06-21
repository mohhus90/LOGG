@extends('admin.layouts.admin')
@section('title') المناطق الحرة @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') المناطق الحرة @endsection

@section('content')
<div class="col-12 col-md-8 offset-md-2">

  <div class="card card-outline card-dark">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-warehouse ml-2"></i>
        إدارة عملاء المناطق الحرة
      </h3>
    </div>
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle ml-1"></i>
        العملاء المضافون هنا يُصدَّرون في ملف المبيعات CSV بـ <strong>نوع السلعة = 2</strong> (منطقة حرة).
      </div>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      {{-- نموذج الإضافة --}}
      <form action="{{ route('tax.free_zones.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="row align-items-end">
          <div class="col-md-4">
            <div class="form-group mb-0">
              <label class="font-weight-bold">رقم التسجيل الضريبي <span class="text-danger">*</span></label>
              <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror"
                placeholder="مثال: 655850090" value="{{ old('tax_id') }}" required
                dir="ltr" style="text-align:left">
              @error('tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-5">
            <div class="form-group mb-0">
              <label class="font-weight-bold">اسم العميل (اختياري)</label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="مثال: ماجيك لاند الحكير" value="{{ old('name') }}">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-dark btn-block">
              <i class="fas fa-plus ml-1"></i> إضافة
            </button>
          </div>
        </div>
      </form>

      <hr>

      {{-- جدول العملاء --}}
      @if($zones->isEmpty())
        <p class="text-muted text-center py-3">لا يوجد عملاء مناطق حرة مضافون بعد.</p>
      @else
        <table class="table table-bordered table-hover table-sm">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>رقم التسجيل الضريبي</th>
              <th>اسم العميل</th>
              <th>تاريخ الإضافة</th>
              <th>حذف</th>
            </tr>
          </thead>
          <tbody>
            @foreach($zones as $i => $zone)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td dir="ltr" class="ltr-text"><code>{{ $zone->tax_id }}</code></td>
              <td>{{ $zone->name ?? '—' }}</td>
              <td>{{ $zone->created_at->format('Y-m-d') }}</td>
              <td>
                <form action="{{ route('tax.free_zones.destroy', $zone) }}" method="POST"
                  onsubmit="return confirm('حذف هذا العميل من قائمة المناطق الحرة؟')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif

    </div>
  </div>
</div>
@endsection
