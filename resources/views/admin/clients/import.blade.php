@extends('admin.layouts.admin')
@section('title') استيراد موظفي {{ $client->client_name }} @endsection
@section('start') العملاء @endsection
@section('home') <a href="{{ route('clients.index') }}">العملاء</a> @endsection
@section('startpage') استيراد CSV @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                استيراد موظفي العميل: <strong>{{ $client->client_name }}</strong>
            </h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Instructions --}}
            <div class="alert alert-info">
                <strong>تعليمات الاستيراد:</strong>
                <ul class="mb-0 mt-1">
                    <li>يجب أن يكون الملف بصيغة <code>.csv</code></li>
                    <li>يجب أن يحتوي الملف على الأعمدة التالية في الصف الأول (Header):</li>
                </ul>
                <code class="ltr-text d-block mt-1 small" style="text-align:left; padding:8px; background:#f8f9fa; border-radius:4px;">
                    SR, Fake ID, HRID, English Name, Arabic Name, Position, Mobile, Reference Number,
                    Relative, Address, NID, Gender, Date Of Birth, Age, Marital Status, Status,
                    Hiring Date, Resignation Date, Hiring Documents, Military Certificate,
                    Social Number, Start Date Of Social, Form 1 Comments, End Date Of Social, Form 6 Comments, Comments
                </code>
                <ul class="mb-0 mt-2">
                    <li>الموظفون الموجودون مسبقاً (بنفس الـ NID أو HRID) سيتم <strong>تخطيهم</strong> تلقائياً دون تكرار</li>
                    <li>الصفوف الفارغة ستُتجاهل تلقائياً</li>
                    <li>يجب وجود وردية (Shift) مضافة في النظام قبل الاستيراد</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('clients.import.csv', $client->id) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="form-group row mb-4">
                    <label class="col-sm-2 col-form-label">اختر ملف CSV <span class="text-danger">*</span></label>
                    <div class="col-sm-5">
                        <input type="file" class="form-control" name="csv_file" accept=".csv,.txt" required>
                        @error('csv_file')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Current stats --}}
                <div class="alert alert-secondary">
                    <strong>الموظفون الحاليون للعميل:</strong>
                    {{ $client->employees()->count() }} موظف
                    &nbsp;|&nbsp;
                    <a href="{{ route('employees.index', ['client_id' => $client->id]) }}">عرضهم</a>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg col-3">
                        <i class="fas fa-file-import"></i> بدء الاستيراد
                    </button>
                    <a class="btn btn-warning btn-lg col-2" href="{{ route('clients.index') }}">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
