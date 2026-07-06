@extends('admin.layouts.assets')
@section('title') تشغيل الإهلاك الشهري @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_depreciation.form') }}">تشغيل الإهلاك</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-6">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line ml-2"></i> تشغيل إهلاك الأصول الشهري</h3></div>
        <form action="{{ route('asset_depreciation.run') }}" method="POST" onsubmit="return confirm('تشغيل إهلاك هذا الشهر لكل الأصول النشطة؟')">
            @csrf
            <div class="card-body">
                <p class="text-muted">
                    سيتم احتساب قسط الإهلاك الشهري (بطريقة القسط الثابت) لكل الأصول النشطة التي لم يُشغَّل
                    لها إهلاك هذا الشهر بعد، وترحيل قيد محاسبي مجمّع لكل فئة أصول.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>السنة</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الشهر</label>
                            <select name="month" class="form-control" required>
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-play ml-1"></i> تشغيل الإهلاك</button>
                <a href="{{ route('asset_depreciation.history') }}" class="btn btn-secondary">سجل التشغيل السابق</a>
            </div>
        </form>
    </div>
</div>
@endsection
