@extends('admin.layouts.admin')
@section('title') تعديل خصم @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('deductions.index') }}">الخصومات</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل خصم — {{ $deduction->employee->employee_name_A ?? '' }}
            </h3>
        </div>
        <form action="{{ route('deductions.update', $deduction->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="form-group">
                    <label>الموظف</label>
                    <input type="text" class="form-control bg-light" readonly
                        value="{{ $deduction->employee->employee_name_A ?? '-' }}">
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>تاريخ الخصم</label>
                        <input type="date" name="deduction_date" class="form-control"
                            value="{{ old('deduction_date', $deduction->deduction_date) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>نوع الخصم</label>
                        <input type="text" name="deduction_type" class="form-control"
                            value="{{ old('deduction_type', $deduction->deduction_type) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>القيمة</label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control"
                                step="0.01" min="0.01"
                                value="{{ old('amount', $deduction->amount) }}">
                            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الشهر</label>
                        <select name="month" class="form-control">
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ $deduction->month==$i+1?'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>السنة</label>
                        <input type="number" name="year" class="form-control"
                            value="{{ old('year', $deduction->year) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الحالة</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $deduction->status==1?'selected':'' }}>معتمدة</option>
                            <option value="2" {{ $deduction->status==2?'selected':'' }}>معلقة</option>
                            <option value="3" {{ $deduction->status==3?'selected':'' }}>ملغاة</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>ملاحظات</label>
                        <input type="text" name="notes" class="form-control"
                            value="{{ old('notes', $deduction->notes) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('deductions.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
