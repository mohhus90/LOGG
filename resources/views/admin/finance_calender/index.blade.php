@extends('admin.layouts.admin')
@section('title')
السنوات المالية
@endsection
@section('start')
السنوات المالية
@endsection
@section('home')
<a href="{{ route('finance_calender.index') }}"> السنوات المالية</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات السنوات المالية
              <a class="btn btn-success" href="finance_calender.create">اضافة جديد</a>
            </h3>
        </div>
        <div class="card-body">
          @if(@isset($data) and !@empty($data) )
          <table class="table table-bordered">
              <thead>
                <th scope="col">كود السنة</th>
                <th scope="col">وصف السنة</th>
                <th scope="col">تاريخ البداية</th>
                <th scope="col">تاريخ النهاية</th>
                <th scope="col">الاضافة بواسطة</th>
                <th scope="col">التحديث بواسطة</th>
                <th scope="col">الاضافة بتاريخ</th>
                <th scope="col">التحديث بتاريخ</th>
                
              </thead>
              <tbody>
                @foreach ($data as $info)
                
                  
                  <tr>
                    <td> {{ $info->finance_yr }}</td>
                    <td> {{ $info->finance_yr_desc }}</td>
                    <td> {{ $info->start_date }}</td>
                    <td> {{ $info->end_date }}</td>
                    <td> {{ $info->added_by }}</td>
                    <td> {{ $info->updated_by }}</td>
                    <td> {{ $info->created_at }}</td>
                    <td> {{ $info->updated_at }}</td>                   
                  </tr>
                  
                  
                @endforeach
              </tbody>
            </table>
          @else
          <h2>لا توجد بيانات للعرض</h2>
          @endif
        </div>
    </div>
</div>
   
@endsection
