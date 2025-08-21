@extends('admin.layouts.admin')
@section('title')
    الضبط العام
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('generalsetting.index') }}"> الضبط</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الضبط العام للشركة
              <a class="btn btn-success" href="{{ route('generalsetting.create') }}">اضافة شركة جديدة</a>
            </h3>
        </div>
       
        <div class="card-body">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
              <tbody>
                  <tr>
                    <th scope="col">اسم الشركة</th>
                    <th scope="col">{{ $data['com_name'] }}</th>
                  </tr>
                  <tr>
                    <th scope="row">كود الشركة</th>
                    <td> {{ $data['com_code'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">حالة الشركة</th>
                    <td> @if ($data['saysem_status']=0) معطل @else مفعل @endif</td>
                  </tr>
                  
                  <tr>
                    <th scope="row">تليفون الشركة</th>
                    <td> {{ $data['phone'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">ايميل الشركة</th>
                    <td> {{ $data['email'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">بعد كم دقيقة تحسب تأخير حضور</th>
                    <td> {{ $data['after_minute_calc_delay'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">بعد كم دقيقة تحسب انصراف مبكر</th>
                    <td> {{ $data['after_minute_calc_early'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">بعد كم دقيقة مجموع الانصراف المبكر والحضور المتأخر تخصم ربع يوم</th>
                    <td> {{ $data['after_minute_quarterday'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">بعد كم مرة تأخير أو انصراف مبكر يخصم نصف يوم	</th>
                    <td> {{ $data['after_time_half_daycut'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	بعد كم مرة تأخير أو انصراف مبكر يخصم يوم</th>
                    <td> {{ $data['after_time_allday_daycut'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	رصيد اجازات الموظف الشهرى</th>
                    <td> {{ $data['monthly_vacation_balance'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">رصيد الاجازات الاولى بعد مدة 6 شهور مثلا</th>
                    <td> {{ $data['first_balance_begain_vacation'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	بعد كم يوم ينزل للموظف رصيد اجازات</th>
                    <td> {{ $data['after_days_begain_vacation'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	قيمة خصم الايام بعد اول مرة غياب</th>
                    <td> {{ $data['sanctions_value_first_abcence'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	قيمة خصم الايام بعد اول ثانى غياب</th>
                    <td> {{ $data['sanctions_value_second_abcence'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">قيمة خصم الايام بعد اول ثالث غياب</th>
                    <td> {{ $data['sanctions_value_third_abcence'] }}</td>
                  </tr>
                  <tr>
                    <th scope="row">	قيمة خصم الايام بعد اول رابع غياب</th>
                    <td> {{ $data['sanctions_value_forth_abcence'] }}</td>
                  </tr>
                    <td class="text-center" colspan="2" ><a class="btn btn-primary btn-lg col-2" href="{{ route('generalsetting.edit') }}">تعديل</a></td>
                  </tr>
                </tbody>
              </table>
              {{-- <br>
              {{ $data->links('pagination::bootstrap-5') }} --}}
            @else
            <h2>لا توجد بيانات للعرض</h2>
            @endif
        </div>
    </div>
</div>
   
@endsection
