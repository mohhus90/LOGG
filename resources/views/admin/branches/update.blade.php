@extends('admin.layouts.admin')
@section('title')
{{ __('admin.branches_title') }}
@endsection
@section('start')
    {{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('branches.index') }}">{{ __('admin.branches_title') }}</a>
@endsection
@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.edit_branch') }}</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('branches.update',$data['id']) }}">
                @csrf
                <div class="form-group row">
                  <label for="branch_name" class="col-sm-2 col-form-label ">{{ __('admin.branch_name') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="branch_name" id="branch_name" value="{{ old('branch_name',$data['branch_name']) }}" >
                  </div>
                  @error('branch_name')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="address" class="col-sm-2 col-form-label ">{{ __('admin.branch_address') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="address" id="address" value="{{ old('address',$data['address']) }}" >
                  </div>
                  @error('address')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="phone" class="col-sm-2 col-form-label ">{{ __('admin.branch_phone') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone',$data['phone']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label ">{{ __('admin.branch_email') }}</label>
                  <div class="col-sm-5">
                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email',$data['email']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="active" class="col-sm-2 col-form-label ">{{ __('admin.branch_status') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="active" id="active" value="{{ old('active',$data['active']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">موقع الفرع (اختياري)</label>
                  <div class="col-sm-3">
                    <input type="text" class="form-control" name="latitude" placeholder="Latitude" value="{{ old('latitude', $data['latitude']) }}">
                  </div>
                  <div class="col-sm-3">
                    <input type="text" class="form-control" name="longitude" placeholder="Longitude" value="{{ old('longitude', $data['longitude']) }}">
                  </div>
                  <div class="col-sm-3">
                    <input type="number" class="form-control" name="geofence_radius_m" placeholder="نطاق السماح (متر)" value="{{ old('geofence_radius_m', $data['geofence_radius_m']) }}">
                  </div>
                  <small class="col-sm-10 offset-sm-2 text-muted">إذا تُركت فارغة، لن يتم التحقق من موقع الموظف عند تسجيل الحضور من التطبيق — يُسجَّل فقط للمراجعة.</small>
                </div>
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">{{ __('admin.update') }}</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('branches.index') }}">{{ __('admin.cancel') }}</a>
                </div>
              </form>
        </div>
    </div>
</div>
@endsection
