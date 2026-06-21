@extends('admin.layouts.admin')
@section('title')
{{ __('admin.emp_title') }}
@endsection
@section('start')
{{ __('admin.general_settings') }}
@endsection
@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection
@section('startpage')
    {{ __('admin.view') }}
@endsection

@section('content')
  <div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_upload_excel_title') }}</h3>
        </div>

        <div class="card-body">
          <form enctype="multipart/form-data" action="{{ route('employees.douploadexcel') }}" method="post">
            @csrf
            <div class="col-ms-12">
              <div class="form-group">
                  <label>{{ __('admin.emp_choose_excel') }}</label>
                  <input type="file" name="excel_file" id="excel_file" class="form-contrlo">
              </div>
              @error('excel_file')
               <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-ms-12">
              <div class="form-group text-center">
                <button class="btn btn-sm btn-success" type="submit" name="submit">{{ __('admin.emp_attach_file') }}</button>
                <a href="{{ route('employees.index') }}" class="btn btn-danger btn-sm">{{ __('admin.cancel') }}</a>
              </div>
            </div>
          </form>
        </div>
    </div>
  </div>
@endsection
