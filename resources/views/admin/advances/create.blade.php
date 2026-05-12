@extends('admin.layouts.admin')
@section('title') إضافة سلفة @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('advances.index') }}">السلف</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i>إضافة سلفة جديدة</h3>
        </div>
        <form action="{{ route('advances.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="form-group">
                    <label>الموظف <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                            {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>تاريخ السلفة <span class="text-danger">*</span></label>
                        <input type="date" name="advance_date" class="form-control" required
                            value="{{ old('advance_date', today()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>قيمة السلفة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control" id="amountInput"
                                step="0.01" min="1" required value="{{ old('amount') }}"
                                placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>عدد أقساط السداد <span class="text-danger">*</span></label>
                        <input type="number" name="installments" class="form-control" id="installmentsInput"
                            min="1" required value="{{ old('installments', 1) }}"
                            placeholder="عدد الأشهر">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>القسط الشهري (محتسب تلقائياً)</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="monthlyCalc"
                                readonly placeholder="سيظهر بعد إدخال القيمة والأقساط">
                            <div class="input-group-append">
                                <span class="input-group-text">ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="أي ملاحظات إضافية">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('advances.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
function calcMonthly() {
    const amount = parseFloat(document.getElementById('amountInput').value) || 0;
    const inst   = parseInt(document.getElementById('installmentsInput').value) || 1;
    const monthly = inst > 0 ? (amount / inst).toFixed(2) : '0.00';
    document.getElementById('monthlyCalc').value = monthly;
}
document.getElementById('amountInput').addEventListener('input', calcMonthly);
document.getElementById('installmentsInput').addEventListener('input', calcMonthly);
calcMonthly();
</script>
@endsection
