@extends('admin.layouts.admin')
@section('title') نسخ السلف من شهر سابق @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('advances.index') }}">السلف</a> @endsection
@section('startpage') نسخ @endsection

@section('content')
<div class="col-md-6 mx-auto">
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-copy ml-2"></i>نسخ السلف من شهر سابق</h3>
        </div>
        <form action="{{ route('advances.copy_month') }}" method="POST">
            @csrf
            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle ml-1"></i>
                    سيتم نسخ جميع السلف الجارية أو غير الملغاة من الشهر المختار وإنشاؤها كسلف جديدة بتاريخ تحدده.
                </div>

                <div class="form-group">
                    <label>الشهر المصدر (المراد النسخ منه) <span class="text-danger">*</span></label>
                    @if($months->isEmpty())
                        <div class="alert alert-warning">لا توجد سلف مسجلة سابقاً للنسخ منها.</div>
                    @else
                    <select name="source_month" class="form-control" required>
                        <option value="">-- اختر الشهر --</option>
                        @foreach($months as $ym => $label)
                        <option value="{{ $ym }}" {{ old('source_month')==$ym?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                <div class="form-group">
                    <label>تاريخ السلف الجديدة <span class="text-danger">*</span></label>
                    <input type="date" name="target_date" class="form-control" required
                           value="{{ old('target_date', today()->format('Y-m-d')) }}">
                    <small class="text-muted">سيُستخدم هذا التاريخ لجميع السلف المنسوخة</small>
                </div>

            </div>
            <div class="card-footer">
                @if($months->isNotEmpty())
                <button type="submit" class="btn btn-info"
                        onclick="return confirm('هل تريد نسخ سلف الشهر المختار؟ سيتم إنشاء سجلات جديدة.')">
                    <i class="fas fa-copy ml-1"></i> نسخ السلف
                </button>
                @endif
                <a href="{{ route('advances.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
