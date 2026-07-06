@extends('admin.layouts.admin')
@section('title') تعديل مكافأة @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('bonuses.index') }}">المكافآت</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-7 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل مكافأة — {{ $bonus->employee->employee_name_A ?? '' }}
            </h3>
        </div>
        <form action="{{ route('bonuses.update', $bonus->id) }}" method="POST" id="bonusForm">
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
                        value="{{ $bonus->employee->employee_name_A ?? '-' }}">
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>تاريخ المكافأة <span class="text-danger">*</span></label>
                        <input type="date" name="bonus_date" class="form-control" required
                            value="{{ old('bonus_date', $bonus->bonus_date) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>نوع المكافأة <span class="text-danger">*</span></label>
                        <select name="bonus_type" id="bonus_type" class="form-control" required onchange="toggleBonusType()">
                            <option value="1" {{ $bonus->bonus_type==1?'selected':'' }}>مبلغ ثابت</option>
                            <option value="2" {{ $bonus->bonus_type==2?'selected':'' }}>أيام × مضاعف اليوم</option>
                        </select>
                    </div>
                </div>

                {{-- حقل المبلغ الثابت --}}
                <div id="fixed_amount_section" class="row">
                    <div class="col-md-12 form-group">
                        <label>المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="amount" class="form-control"
                                step="0.01" min="0.01"
                                value="{{ old('amount', $bonus->amount) }}"
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
                                step="0.5" min="0.5"
                                value="{{ old('days', $bonus->days) }}"
                                placeholder="مثال: 1 أو 2.5">
                            <div class="input-group-append"><span class="input-group-text">يوم</span></div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>مضاعف اليوم</label>
                        <input type="number" name="day_multiplier" id="day_multiplier" class="form-control"
                            step="0.01" min="0.01"
                            value="{{ old('day_multiplier', $bonus->day_multiplier ?? 1) }}"
                            placeholder="افتراضي 1">
                        <small class="text-muted">القيمة = أيام × معدل اليوم × المضاعف</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>الشهر</label>
                        <select name="month" class="form-control">
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ $bonus->month==$i+1?'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>السنة</label>
                        <input type="number" name="year" class="form-control"
                            value="{{ old('year', $bonus->year) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الحالة</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $bonus->status==1?'selected':'' }}>معتمدة</option>
                            <option value="2" {{ $bonus->status==2?'selected':'' }}>معلقة</option>
                            <option value="3" {{ $bonus->status==3?'selected':'' }}>ملغاة</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes', $bonus->notes) }}">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
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
toggleBonusType();
</script>
@endsection
