@extends('admin.layouts.admin')
@section('title') تسجيل حضور @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-8 mx-auto">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i>تسجيل حضور وانصراف</h3>
        </div>
        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الموظف <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required id="employeeSelect">
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                data-shift="{{ $emp->shifts_type->type ?? '' }}"
                                data-from="{{ $emp->shifts_type->from_time ?? '' }}"
                                data-to="{{ $emp->shifts_type->to_time ?? '' }}"
                                {{ old('employee_id')==$emp->id?'selected':'' }}>
                                {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>الشيفت المحدد للموظف</label>
                        <input type="text" class="form-control bg-light" id="shiftDisplay" readonly
                            placeholder="سيظهر بعد اختيار الموظف">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                            value="{{ old('attendance_date', today()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>وقت الحضور</label>
                        <input type="time" name="check_in_time" class="form-control"
                            value="{{ old('check_in_time') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>وقت الانصراف</label>
                        <input type="time" name="check_out_time" class="form-control"
                            value="{{ old('check_out_time') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ old('status',1)==1?'selected':'' }}>حضر</option>
                            <option value="2" {{ old('status')==2?'selected':'' }}>غياب</option>
                            <option value="3" {{ old('status')==3?'selected':'' }}>إجازة</option>
                            <option value="4" {{ old('status')==4?'selected':'' }}>إجازة رسمية</option>
                            <option value="5" {{ old('status')==5?'selected':'' }}>مأمورية</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>ملاحظات</label>
                        <input type="text" name="notes" class="form-control"
                            value="{{ old('notes') }}" placeholder="أي ملاحظات اختيارية">
                    </div>
                </div>

                <div class="alert alert-info mt-2" id="shiftInfo" style="display:none">
                    <i class="fas fa-info-circle ml-1"></i>
                    الشيفت: <strong id="shiftInfoText"></strong> —
                    سيتم احتساب التأخير والأوفرتايم تلقائيًا عند الحفظ
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('employeeSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const shift = opt.dataset.shift;
    const from  = opt.dataset.from;
    const to    = opt.dataset.to;
    if (shift) {
        document.getElementById('shiftDisplay').value = shift + ' (' + from + ' - ' + to + ')';
        document.getElementById('shiftInfoText').textContent = shift + ' من ' + from + ' إلى ' + to;
        document.getElementById('shiftInfo').style.display = 'block';
    } else {
        document.getElementById('shiftDisplay').value = '';
        document.getElementById('shiftInfo').style.display = 'none';
    }
});
</script>
@endsection