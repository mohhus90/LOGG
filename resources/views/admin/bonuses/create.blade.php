@extends('admin.layouts.admin')
@section('title') إضافة مكافأة @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('bonuses.index') }}">المكافآت</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-gift ml-2"></i>إضافة مكافأة جديدة</h3>
        </div>
        <form action="{{ route('bonuses.store') }}" method="POST" id="bonusForm">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="form-group">
                    <label>الموظف <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-control select2" required>
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
                        <label>تاريخ المكافأة <span class="text-danger">*</span></label>
                        <input type="date" name="bonus_date" class="form-control" required
                            value="{{ old('bonus_date', today()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>نوع المكافأة <span class="text-danger">*</span></label>
                        <select name="bonus_type" id="bonus_type" class="form-control" required onchange="toggleBonusType()">
                            <option value="1" {{ old('bonus_type',1)==1?'selected':'' }}>مبلغ ثابت</option>
                            <option value="2" {{ old('bonus_type')==2?'selected':'' }}>أيام × مضاعف اليوم</option>
                        </select>
                    </div>
                </div>

                {{-- حقل المبلغ الثابت --}}
                <div id="fixed_amount_section" class="row">
                    <div class="col-md-12 form-group">
                        <label>المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="amount" class="form-control"
                                step="0.01" min="0.01" value="{{ old('amount') }}"
                                placeholder="أدخل المبلغ">
                            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                        </div>
                    </div>
                </div>

                {{-- حقول الأيام والمضاعف --}}
                <div id="days_section" class="row" style="display:none">
                    <div class="col-md-6 form-group">
                        <label>عدد الأيام <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="days" id="days" class="form-control"
                                step="0.5" min="0.5" value="{{ old('days') }}"
                                placeholder="مثال: 1 أو 2.5">
                            <div class="input-group-append"><span class="input-group-text">يوم</span></div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>مضاعف اليوم</label>
                        <input type="number" name="day_multiplier" id="day_multiplier" class="form-control"
                            step="0.01" min="0.01" value="{{ old('day_multiplier', 1) }}"
                            placeholder="افتراضي 1">
                        <small class="text-muted">القيمة = أيام × معدل اليوم × المضاعف</small>
                    </div>
                </div>

                <div class="row">
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
                    <div class="col-md-4 form-group">
                        <label>الحالة</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status',1)==1?'selected':'' }}>معتمدة</option>
                            <option value="2" {{ old('status')==2?'selected':'' }}>معلقة</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes') }}" placeholder="ملاحظات اختيارية">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('bonuses.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
function toggleBonusType() {
    var type = document.getElementById('bonus_type').value;
    var fixedSection = document.getElementById('fixed_amount_section');
    var daysSection  = document.getElementById('days_section');
    var amountInput  = document.getElementById('amount');
    var daysInput    = document.getElementById('days');

    if (type == '2') {
        fixedSection.style.display = 'none';
        daysSection.style.display  = '';
        amountInput.removeAttribute('required');
        daysInput.setAttribute('required', 'required');
    } else {
        fixedSection.style.display = '';
        daysSection.style.display  = 'none';
        daysInput.removeAttribute('required');
        amountInput.setAttribute('required', 'required');
    }
}
// تطبيق الحالة الأولية
toggleBonusType();
</script>
@endsection
