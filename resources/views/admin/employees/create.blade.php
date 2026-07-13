@extends('admin.layouts.admin')

@section('title')
{{ __('admin.emp_title') }}
@endsection

@section('start')
    {{ __('admin.hr_management') }}
@endsection

@section('css')
<style>
    .tab-content {
        padding: 15px;
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0 0 0.25rem 0.25rem;
    }
    .form-group { margin-bottom: 1rem; }
    select.form-control {
        height: auto !important;
        line-height: 1.5 !important;
        padding-top: 0.45rem !important;
        padding-bottom: 0.45rem !important;
    }
</style>
@endsection

@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection

@section('startpage')
{{ __('admin.add') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_add_new') }}</h3>
        </div>
        <div class="card-body">
            @include('admin.employees._form', ['mode' => 'create', 'data' => null])
        </div>
    </div>
</div>
@endsection

@section("script")
    <script>
        window.EMP_DICT_GET_URL  = '{{ route("employees.dictionary.get") }}';
        window.EMP_DICT_SAVE_URL = '{{ route("employees.dictionary.save") }}';
        window.EMP_CSRF_TOKEN    = '{{ csrf_token() }}';
    </script>
    <script src="{{ asset('assets/admin/js/employee-name-dictionary.js') }}"></script>
    <script src="{{ asset('assets/admin/js/employee-form-tabs.js') }}"></script>
@endsection
