@extends('admin.layouts.system')
@section('title') بيانات شركتي @endsection
@section('start') النظام @endsection
@section('home') <a href="{{ route('company_profile.edit') }}">بيانات شركتي</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-12">
<div class="card card-primary card-outline">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-building ml-2"></i>بيانات شركتي</h3>
  </div>

@if(!isset($setting) || empty($setting))
  <div class="card-body">
    <div class="alert alert-warning">لا توجد بيانات شركة مسجّلة بعد.</div>
  </div>
@else

<form method="POST" action="{{ route('company_profile.update') }}" enctype="multipart/form-data">
  @csrf

  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
      </div>
    @endif
    @if(session('errorUpdate'))
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('errorUpdate') }}
      </div>
    @endif

    <div class="row">
      <div class="col-md-4 form-group">
        <label>اسم الشركة <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="com_name"
          value="{{ old('com_name', $setting->com_name) }}" required>
      </div>
      <div class="col-md-4 form-group">
        <label>هاتف الشركة</label>
        <input type="text" class="form-control" name="phone"
          value="{{ old('phone', $setting->phone) }}" placeholder="01xxxxxxxxx">
      </div>
      <div class="col-md-4 form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" class="form-control" name="email"
          value="{{ old('email', $setting->email) }}">
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 form-group">
        <label>العنوان</label>
        <input type="text" class="form-control" name="address"
          value="{{ old('address', $setting->address) }}">
      </div>
      <div class="col-md-2 form-group">
        <label>حالة النظام</label>
        <select class="form-control" name="saysem_status">
          <option value="1" {{ ($setting->saysem_status ?? 1) == 1 ? 'selected' : '' }}>✅ مفعّل</option>
          <option value="0" {{ ($setting->saysem_status ?? 1) == 0 ? 'selected' : '' }}>❌ معطّل</option>
        </select>
      </div>
      <div class="col-md-4 form-group">
        <label>شعار الشركة (Logo)</label>
        <div class="d-flex align-items-center">
          @if($setting->image)
            <img src="{{ asset('storage/' . $setting->image) }}"
              alt="Logo" style="height:50px;margin-left:10px;border-radius:6px;border:1px solid #dee2e6;object-fit:contain;padding:2px">
          @endif
        </div>
        <input type="file" name="logo_file" class="form-control-file mt-1" accept="image/*"
          onchange="previewLogo(this)">
        <small class="text-muted">PNG, JPG, SVG — أقصى 2MB</small>
        <div id="logoPreview" class="mt-1"></div>
      </div>
    </div>
  </div>{{-- end card-body --}}

  <div class="card-footer">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save ml-1"></i> حفظ الإعدادات
    </button>
  </div>
</form>

@endif
</div>
</div>
@endsection

@section('script')
<script>
function previewLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('logoPreview').innerHTML =
        '<img src="' + e.target.result + '" style="height:55px;border-radius:6px;border:1px solid #dee2e6;margin-top:4px">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
@endsection
