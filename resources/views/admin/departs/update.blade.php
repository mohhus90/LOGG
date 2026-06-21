@extends('admin.layouts.admin')
@section('title')
{{ __('admin.departments_title') }}
@endsection
@section('start')
    {{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('departs.index') }}">{{ __('admin.departments_title') }}</a>
@endsection
@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.edit_dept') }}</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('departs.update',$data['id']) }}">
                @csrf
                <div class="form-group row">
                  <label for="dep_name" class="col-sm-2 col-form-label ">{{ __('admin.dept_name') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="dep_name" id="dep_name" value="{{ old('dep_name',$data['dep_name']) }}" >
                  </div>
                  @error('dep_name')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="phone" class="col-sm-2 col-form-label ">{{ __('admin.dept_phone') }}</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone',$data['phone']) }}" >
                  </div>
                  @error('phone')
                  <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label ">{{ __('admin.dept_email') }}</label>
                  <div class="col-sm-5">
                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email',$data['email']) }}" >
                  </div>
                  <div class="form-group row">
                    <label for="notes" class="col-sm-2 col-form-label ">{{ __('admin.notes') }}</label>
                    <div class="col-sm-5">
                      <input type="text" class="form-control" name="notes" id="notes" value="{{ old('notes',$data['notes']) }}" >
                    </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">{{ __('admin.update') }}</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('departs.index') }}">{{ __('admin.cancel') }}</a>
                </div>
              </form>
        </div>
    </div>
</div>
@endsection
