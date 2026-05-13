@extends('admin.layouts.admin')
@section('title') تعديل سلفة @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('advances.index') }}">السلف</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل سلفة — {{ $advance->employee->employee_name_A ?? '' }}
            </h3>
        </div>
        <form action="{{ route('advances.update', $advance->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الموظف</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $advance->employee->employee_name_A ?? '-' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>تاريخ السلفة</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $advance->advance_date }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>قيمة السلفة الأصلية</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" readonly
                                value="{{ number_format($advance->amount, 2) }}">
                            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>المتبقي للسداد</label>
                        <div class="input-group">
                            <input type="number" name="remaining_amount" class="form-control"
                                step="0.01" min="0"
                                value="{{ old('remaining_amount', $advance->remaining_amount) }}">
                            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>عدد الأقساط <span class="text-danger">*</span></label>
                        <input type="number" name="installments" class="form-control" id="instInput"
                            min="1" required value="{{ old('installments', $advance->installments) }}">
                        <small class="text-muted">
                            القسط الشهري سيكون:
                            <strong id="instCalc">{{ number_format($advance->monthly_installment, 2) }}</strong> ج.م
                        </small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ $advance->status==1?'selected':'' }}>جارية</option>
                            <option value="2" {{ $advance->status==2?'selected':'' }}>مسددة</option>
                            <option value="3" {{ $advance->status==3?'selected':'' }}>ملغاة</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>ملاحظات</label>
                        <input type="text" name="notes" class="form-control"
                            value="{{ old('notes', $advance->notes) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('advances.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
const originalAmount = {{ $advance->amount }};
document.getElementById('instInput').addEventListener('input', function() {
    const inst = parseInt(this.value) || 1;
    document.getElementById('instCalc').textContent = (originalAmount / inst).toFixed(2);
});
</script>
@endsection
