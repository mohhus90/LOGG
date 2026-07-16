@extends('admin.layouts.admin')
@section('title') استيراد مؤثرات رواتب {{ $client->client_name }} @endsection
@section('start') الرواتب @endsection
@section('home') <a href="{{ route('payroll.index') }}">كشف الرواتب</a> @endsection
@section('startpage') استيراد مؤثرات {{ $client->client_name }} @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                استيراد مؤثرات رواتب العميل: <strong>{{ $client->client_name }}</strong>
            </h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info">
                <strong>تعليمات:</strong>
                <ul class="mb-0 mt-1">
                    <li>ارفع ملف Excel الشهري الخاص بهذا العميل (بأي ترتيب أعمدة — سيُطلب منك تحديد كل عمود فى الخطوة التالية)</li>
                    <li>حدد الصف الذى يحتوى على عناوين الأعمدة (Header Row) داخل الملف</li>
                    <li>يجب أن يحتوي الملف على عمود "كود الموظف" ليتم مطابقة كل صف بموظفه</li>
                    @if($template)
                        <li class="text-success">
                            <i class="fas fa-check-circle"></i>
                            يوجد ربط أعمدة محفوظ مسبقًا لهذا العميل من استيراد سابق — سيتم اقتراحه تلقائيًا فى الخطوة التالية
                        </li>
                    @endif
                </ul>
            </div>

            <form method="POST" action="{{ route('payroll_factors.import.preview', $client->id) }}"
                  enctype="multipart/form-data" class="form-inline">
                @csrf
                <div class="form-group mb-3 mr-3">
                    <label class="ml-2">الشهر</label>
                    <select name="month" class="form-control" required>
                        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                        <option value="{{ $i+1 }}" {{ (now()->month)==$i+1?'selected':'' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3 mr-3">
                    <label class="ml-2">السنة</label>
                    <input type="number" name="year" class="form-control" style="width:100px" value="{{ now()->year }}" required>
                </div>
                <div class="form-group mb-3 mr-3">
                    <label class="ml-2">صف عناوين الأعمدة (Header Row)</label>
                    <input type="number" name="header_row" class="form-control" style="width:80px" value="1" min="1" required>
                </div>
                <div class="form-group mb-3 w-100">
                    <label>ملف Excel <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                    @error('file')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="text-center w-100 mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left"></i> التالي: ربط الأعمدة
                    </button>
                    <a class="btn btn-warning btn-lg" href="{{ route('payroll.index') }}">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
