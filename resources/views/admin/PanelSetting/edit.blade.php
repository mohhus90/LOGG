@extends('admin.layouts.admin')
@section('title')
    الضبط العام
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('generalsetting.index') }}">الضبط</a>

@endsection
@section('startpage')
تعديل
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تعديل بيانات الضبط العام للنظام</h3>
        </div>
        <div class="card-body">
            @if(@isset($data) and !@empty($data) )
              <form method="GET" action="{{ route('generalsetting.update') }}">
                @csrf
                <div class="form-group row d-none">
                  <label for="id" class="col-sm-2 col-form-label ">اسم الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="id" id="id" value="{{ old('id',$data['id']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="com_name" class="col-sm-2 col-form-label">اسم الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="com_name" id="com_name" value="{{ old('com_name',$data['com_name']) }}" >
                    @error('com_name')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label for="com_code" class="col-sm-2 col-form-label">كود الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="com_code" id="com_code" value="{{ old('com_code',$data['com_code']) }}" >
                    @error('com_code')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label for="saysem_status" class="col-sm-2 col-form-label">حالة الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="saysem_status" id="saysem_status" value="{{ old('saysem_status',$data['saysem_status']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="phone" class="col-sm-2 col-form-label">هاتف الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone',$data['phone']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="email" class="col-sm-2 col-form-label">ايميل الشركة</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="email" id="email" value="{{ old('email',$data['email']) }}" >
                    @error('email')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_minute_calc_delay" class="col-sm-2 col-form-label">بعد كم دقيقة تحسب تأخير حضور</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_minute_calc_delay" id="after_minute_calc_delay" value="{{ old('after_minute_calc_delay',$data['after_minute_calc_delay']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_minute_calc_early" class="col-sm-2 col-form-label">بعد كم دقيقة تحسب انصراف مبكر</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_minute_calc_early" id="after_minute_calc_early" value="{{ old('after_minute_calc_early',$data['after_minute_calc_early']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_minute_quarterday" class="col-sm-2 col-form-label">بعد كم دقيقة مجموع الانصراف المبكر والحضور المتأخر تخصم ربع يوم</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_minute_quarterday" id="after_minute_quarterday" value="{{ old('after_minute_quarterday',$data['after_minute_quarterday']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_time_half_daycut" class="col-sm-2 col-form-label">بعد كم مرة تأخير أو انصراف مبكر يخصم نصف يوم</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_time_half_daycut" id="after_time_half_daycut" value="{{ old('after_time_half_daycut',$data['after_time_half_daycut']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_time_allday_daycut" class="col-sm-2 col-form-label">بعد كم مرة تأخير أو انصراف مبكر يخصم يوم</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_time_allday_daycut" id="after_time_allday_daycut" value="{{ old('after_time_allday_daycut',$data['after_time_allday_daycut']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="monthly_vacation_balance" class="col-sm-2 col-form-label">رصيد اجازات الموظف الشهرى</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="monthly_vacation_balance" id="monthly_vacation_balance" value="{{ old('monthly_vacation_balance',$data['monthly_vacation_balance']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="first_balance_begain_vacation" class="col-sm-2 col-form-label">رصيد الاجازات الاولى بعد مدة 6 شهور</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="first_balance_begain_vacation" id="first_balance_begain_vacation" value="{{ old('first_balance_begain_vacation',$data['first_balance_begain_vacation']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="after_days_begain_vacation" class="col-sm-2 col-form-label">بعد كم يوم ينزل للموظف رصيد اجازات</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="after_days_begain_vacation" id="after_days_begain_vacation" value="{{ old('after_days_begain_vacation',$data['after_days_begain_vacation']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="sanctions_value_first_abcence" class="col-sm-2 col-form-label">قيمة خصم الايام بعد اول مرة غياب</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="sanctions_value_first_abcence" id="sanctions_value_first_abcence" value="{{ old('sanctions_value_first_abcence',$data['sanctions_value_first_abcence']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="sanctions_value_second_abcence" class="col-sm-2 col-form-label">قيمة خصم الايام بعد اول ثانى غياب</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="sanctions_value_second_abcence" id="sanctions_value_second_abcence" value="{{ old('sanctions_value_second_abcence',$data['sanctions_value_second_abcence']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="sanctions_value_third_abcence" class="col-sm-2 col-form-label">قيمة خصم الايام بعد اول ثالث غياب</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="sanctions_value_third_abcence" id="sanctions_value_third_abcence" value="{{ old('sanctions_value_third_abcence',$data['sanctions_value_third_abcence']) }}" >
                  </div>
                </div>
                <div class="form-group row">
                  <label for="sanctions_value_forth_abcence" class="col-sm-2 col-form-label">قيمة خصم الايام بعد اول رابع غياب</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="sanctions_value_forth_abcence" id="sanctions_value_forth_abcence" value="{{ old('sanctions_value_forth_abcence',$data['sanctions_value_forth_abcence']) }}" >
                  </div>
                </div>
                <div class="row">
                  <button type="submit" class="text-center" colspan="2" ><a class="btn btn-primary btn-lg col-2">تحديث</a></button>
                </div>
                
              </form>
            @else
            <h2>لا توجد بيانات للعرض</h2>
            @endif
        </div>
    </div>
</div>
   
@endsection
