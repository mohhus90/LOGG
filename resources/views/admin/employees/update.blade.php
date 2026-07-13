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
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
    .doc-card {
        border: 2px dashed #e5e7eb; border-radius: 12px;
        padding: 18px 14px; text-align: center;
        transition: all .2s; position: relative;
    }
    .doc-card.has-file { border-style: solid; border-color: #3b82f6; background: #eff6ff; }
    .doc-card .doc-icon { font-size: 2rem; margin-bottom: 8px; color: #9ca3af; }
    .doc-card.has-file .doc-icon { color: #3b82f6; }
    .doc-card .doc-name { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .doc-card .doc-filename { font-size: .73rem; color: #6b7280; margin-bottom: 10px; word-break: break-all; }
    .doc-card .doc-actions { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
    .doc-badge-uploaded {
        position: absolute; top: 8px; right: 8px;
        background: #10b981; color: #fff;
        font-size: .65rem; padding: 2px 7px;
        border-radius: 20px; font-weight: 700;
    }
</style>
@endsection

@section('home')
<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>
@endsection

@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.emp_edit_title') }}</h3>
        </div>
        <div class="card-body">
            @include('admin.employees._form', ['mode' => 'edit'])
        </div>
    </div>

    @include('admin.employees._documents')
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
