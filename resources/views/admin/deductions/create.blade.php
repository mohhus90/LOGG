@extends('admin.layouts.admin')
@section('title') إضافة خصم @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('deductions.index') }}">الخصومات</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i>إضافة خصم جديد</h3>
        </div>
        <form action="{{ route('deductions.store') }}" method="POST">
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
                        <label>تاريخ الخصم <span class="text-danger">*</span></label>
                        <input type="date" name="deduction_date" class="form-control" required
                            value="{{ old('deduction_date', today()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>نوع الخصم</label>
                        <input type="text" name="deduction_type" class="form-control"
                            placeholder="مثال: غرامة، خصم إداري، تلف..."
                            value="{{ old('deduction_type') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>القيمة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control"
                                step="0.01" min="0.01" required value="{{ old('amount') }}">
                            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الشهر <span class="text-danger">*</span></label>
                        <select name="month" class="form-control" required>
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ old('month', now()->month)==$i+1?'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>السنة <span class="text-danger">*</span></label>
                        <input type="number" name="year" class="form-control" required
                            value="{{ old('year', now()->year) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الحالة</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status',1)==1?'selected':'' }}>معتمدة</option>
                            <option value="2" {{ old('status')==2?'selected':'' }}>معلقة</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>ملاحظات</label>
                        <input type="text" name="notes" class="form-control"
                            value="{{ old('notes') }}" placeholder="ملاحظات اختيارية">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('deductions.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
